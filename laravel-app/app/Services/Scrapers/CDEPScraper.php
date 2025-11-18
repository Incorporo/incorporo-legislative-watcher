<?php

namespace App\Services\Scrapers;

use App\Models\BillChange;
use App\Models\BillCommittee;
use App\Models\BillDocument;
use App\Models\BillInitiator;
use App\Models\BillTimeline;
use App\Models\LegislativeBill;
use App\Models\ScrapingJob;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CDEPScraper extends BaseScraper
{
    protected $baseUrl = 'https://www.cdep.ro';

    protected $chamber = 'cdep';

    protected $userAgent = 'Mozilla/5.0 (compatible; RomanianLegislativeWatcher/1.0)';

    protected $delaySeconds = 3; // Respectful rate limiting

    /**
     * Scrape list of bills from CDEP
     *
     * @param  int  $chamber  Chamber ID (1=Senate, 2=Chamber of Deputies)
     * @param  int|null  $year  Filter by year
     * @param  int  $limit  Maximum bills to scrape
     * @return array
     */
    public function scrapeBillList($chamber = 2, $year = null, $limit = null)
    {
        $url = "{$this->baseUrl}/pls/proiecte/upl_pck2015.home";
        if ($chamber) {
            $url .= "?cam={$chamber}";
        }

        Log::info("CDEP: Scraping bill list from {$url}");

        try {
            $response = $this->makeRequest($url);
            $crawler = new Crawler($response);

            $bills = [];
            $count = 0;

            // Extract bill links
            $crawler->filter('a[href*="idp="]')->each(function (Crawler $node) use (&$bills, &$count, $limit) {
                if ($limit && $count >= $limit) {
                    return;
                }

                $href = $node->attr('href');
                preg_match('/idp=(\d+)/', $href, $matches);

                if (isset($matches[1])) {
                    $idp = $matches[1];
                    $title = trim($node->text());

                    // Build full URL
                    $fullUrl = $this->buildFullUrl($href);

                    $bills[] = [
                        'internal_id' => $idp,
                        'title' => $title,
                        'url' => $fullUrl,
                    ];

                    $count++;
                }
            });

            Log::info("CDEP: Found {$count} bills");

            return $bills;

        } catch (\Exception $e) {
            Log::error('CDEP: Error scraping bill list: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape detailed information for a specific bill
     *
     * @param  string  $idp  Bill internal ID
     * @param  int  $chamber  Chamber ID
     * @return array
     */
    public function scrapeBillDetail($idp, $chamber = 2)
    {
        $url = "{$this->baseUrl}/pls/proiecte/upl_pck2015.proiect?idp={$idp}&cam={$chamber}";

        Log::info("CDEP: Scraping bill detail for ID {$idp}");

        try {
            $response = $this->makeRequest($url);
            $crawler = new Crawler($response);

            $data = [
                'chamber' => $this->chamber,
                'internal_id' => $idp,
                'url' => $url,
                'metadata' => [],
            ];

            // Extract title
            $titleNode = $crawler->filter('.headline, h1, .title')->first();
            if ($titleNode->count()) {
                $data['title'] = trim($titleNode->text());
            }

            // Extract bill number and year from title or metadata
            $this->extractBillNumber($data, $crawler);

            // Extract status
            $statusNode = $crawler->filter('td:contains("Stadiul procesului legislativ")')->siblings();
            if ($statusNode->count()) {
                $data['status'] = trim($statusNode->text());
            }

            // Extract type
            $typeNode = $crawler->filter('td:contains("Tip")')->siblings();
            if ($typeNode->count()) {
                $data['type'] = trim($typeNode->text());
            }

            // Extract registration date
            $dateNode = $crawler->filter('td:contains("Data publicării")')->siblings();
            if ($dateNode->count()) {
                $dateText = trim($dateNode->text());
                $data['registration_date'] = $this->parseDate($dateText);
            }

            // Extract urgency status
            $urgencyNode = $crawler->filter('td:contains("Procedură de urgență")')->siblings();
            $data['urgency_status'] = $urgencyNode->count() &&
                stripos($urgencyNode->text(), 'da') !== false;

            // Extract initiators
            $data['initiators'] = $this->extractInitiators($crawler);

            // Extract documents
            $data['documents'] = $this->extractDocuments($crawler);

            // Extract timeline events
            $data['timeline'] = $this->extractTimeline($crawler);

            // Extract description/summary
            $descNode = $crawler->filter('td:contains("Obiectul de reglementare")')->siblings();
            if ($descNode->count()) {
                $data['description'] = trim($descNode->text());
            }

            // Calculate content hash for change detection
            $data['content_hash'] = $this->calculateHash($data);

            $data['last_scraped_at'] = now();

            return $data;

        } catch (\Exception $e) {
            Log::error("CDEP: Error scraping bill {$idp}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract bill number and year from various sources
     */
    protected function extractBillNumber(&$data, Crawler $crawler)
    {
        // Try to find bill number field
        $numberNode = $crawler->filter('td:contains("Număr")')->siblings();
        if ($numberNode->count()) {
            $numberText = trim($numberNode->text());

            // Parse format like "L-123/2025" or "PL-x 456/2024"
            if (preg_match('/([A-Z\-]+)\s*(\d+)\/(\d{4})/i', $numberText, $matches)) {
                $data['bill_number'] = $matches[1].$matches[2];
                $data['year'] = (int) $matches[3];
            } elseif (preg_match('/(\d+)\/(\d{4})/', $numberText, $matches)) {
                $data['bill_number'] = $matches[1];
                $data['year'] = (int) $matches[2];
            }
        }

        // Fallback: try to extract from title
        if (! isset($data['year']) && isset($data['title'])) {
            if (preg_match('/(\d+)\/(\d{4})/', $data['title'], $matches)) {
                $data['bill_number'] = $data['bill_number'] ?? $matches[1];
                $data['year'] = (int) $matches[2];
            }
        }

        // Default to current year if not found
        $data['year'] = $data['year'] ?? date('Y');
        $data['bill_number'] = $data['bill_number'] ?? 'CDEP-'.$data['internal_id'];
    }

    /**
     * Extract initiators/sponsors
     */
    protected function extractInitiators(Crawler $crawler)
    {
        $initiators = [];

        $initNode = $crawler->filter('td:contains("Inițiator")')->siblings();
        if ($initNode->count()) {
            $text = $initNode->text();

            // Check for government
            if (stripos($text, 'guvern') !== false) {
                $initiators[] = [
                    'name' => 'Guvernul României',
                    'type' => 'government',
                    'role' => 'primary',
                ];
            }

            // Extract MP names (often in links)
            $initNode->filter('a[href*="structura.mp"]')->each(function (Crawler $node, $i) use (&$initiators) {
                $initiators[] = [
                    'name' => trim($node->text()),
                    'type' => 'mp',
                    'role' => $i === 0 ? 'primary' : 'co_sponsor',
                    'position' => $i,
                ];
            });
        }

        return $initiators;
    }

    /**
     * Extract documents (PDFs, etc.)
     */
    protected function extractDocuments(Crawler $crawler)
    {
        $documents = [];

        $crawler->filter('a[href*=".pdf"], a[href*=".doc"], a[href*=".docx"]')->each(function (Crawler $node) use (&$documents) {
            $href = $node->attr('href');
            $title = trim($node->text());

            // Determine document type
            $type = 'other';
            if (stripos($title, 'expunere de motive') !== false) {
                $type = 'explanatory_memorandum';
            } elseif (stripos($title, 'formă inițiator') !== false) {
                $type = 'bill_text';
            } elseif (stripos($title, 'raport') !== false) {
                $type = 'committee_report';
            } elseif (stripos($title, 'amendament') !== false) {
                $type = 'amendment';
            }

            $documents[] = [
                'url' => $this->buildFullUrl($href),
                'title' => $title,
                'document_type' => $type,
                'mime_type' => $this->getMimeTypeFromUrl($href),
            ];
        });

        return $documents;
    }

    /**
     * Extract timeline events from CDEP detailed timeline table
     * This is the comprehensive version that captures all details
     */
    protected function extractTimeline(Crawler $crawler)
    {
        $events = [];
        $sequenceOrder = 1;
        $currentChamber = null;
        $currentRound = []; // Track rounds per chamber

        // Find the timeline table (look for table with event date/action headers)
        // The CDEP timeline is in a table after "Derularea procedurii legislative"
        $timelineTable = $crawler->filter('table[width="100%"][border="0"][cellspacing="0"]')->last();

        if (!$timelineTable->count()) {
            Log::warning('CDEP: Timeline table not found');
            return [];
        }

        $timelineTable->filter('tr')->each(function (Crawler $row) use (&$events, &$sequenceOrder, &$currentChamber, &$currentRound) {
            $cells = $row->filter('td');

            if ($cells->count() < 2) {
                return; // Skip spacer/header rows
            }

            // Detect chamber from background color of first cell
            $firstCell = $cells->eq(0);
            $bgColor = $firstCell->attr('bgcolor');
            $chamber = $this->detectChamberFromColor($bgColor);

            if (!$chamber) {
                return; // Not a valid event row
            }

            // Update chamber tracking for round counting
            if ($chamber !== $currentChamber) {
                $currentChamber = $chamber;
                if (!isset($currentRound[$chamber])) {
                    $currentRound[$chamber] = 1;
                } else {
                    $currentRound[$chamber]++;
                }
            }

            // Extract event data
            $event = [
                'sequence_order' => $sequenceOrder++,
                'chamber' => $chamber,
                'chamber_round' => $currentRound[$chamber],
            ];

            // Extract date from first cell (if present and not empty)
            $dateText = trim($firstCell->text());
            if ($dateText && $dateText !== '&nbsp;' && $dateText !== ' ') {
                $event['event_date'] = $this->parseDate($dateText);
            }

            // Extract description - usually in cell index 2 or 3
            // The structure varies, but description is after the date and arrow cells
            $descriptionCell = null;
            for ($i = 0; $i < $cells->count(); $i++) {
                $cellText = trim($cells->eq($i)->text());
                // Skip empty cells, date cells, and arrow cells
                if ($cellText && $cellText !== '&nbsp;' && $cellText !== '→' && !$this->parseDate($cellText)) {
                    $descriptionCell = $cells->eq($i);
                    break;
                }
            }

            if (!$descriptionCell || !$descriptionCell->count()) {
                return; // No description found
            }

            $event['description'] = trim($descriptionCell->text());

            // Classify event type
            $event['event_type'] = $this->classifyTimelineEvent($event['description']);

            // Check if it's an adoption/vote event
            if (stripos($event['description'], 'adoptat') !== false) {
                $event['is_adoption'] = true;
                $event['vote_result'] = 'adoptat';

                // Extract voting details from obs divs
                $descriptionCell->filter('div#obs')->each(function (Crawler $obs) use (&$event) {
                    $obsText = $obs->text();
                    if (stripos($obsText, 'art.76') !== false || stripos($obsText, 'art.') !== false) {
                        $event['vote_details'] = [
                            'constitutional_requirement' => $obsText,
                        ];
                    }
                });
            } else if (stripos($event['description'], 'respins') !== false) {
                $event['is_adoption'] = true;
                $event['vote_result'] = 'respins';
            }

            // Check if this is the final event (publication)
            if (stripos($event['description'], 'publicare lege') !== false) {
                $event['is_final'] = true;
            }

            // Extract deadlines from obs divs
            $event['deadlines'] = [];
            $descriptionCell->filter('div#obs')->each(function (Crawler $obs) use (&$event) {
                $obsText = $obs->text();

                // Deadline patterns: "termen depunere amendamente: 18.11.2025"
                if (preg_match('/termen\s+([^:]+):\s*(\d{2}\.\d{2}\.\d{4})/', $obsText, $m)) {
                    $deadlineType = trim($m[1]);
                    $deadlineDate = $this->parseDate($m[2]);

                    if ($deadlineDate) {
                        $event['deadlines'][] = [
                            'type' => $deadlineType,
                            'date' => $deadlineDate,
                        ];

                        // Set primary deadline fields
                        if (!isset($event['deadline'])) {
                            $event['deadline'] = $deadlineDate;
                            $event['deadline_type'] = $deadlineType;
                        }
                    }
                }
            });

            // Extract documents (PDFs, DOCs)
            $event['documents'] = [];
            $descriptionCell->filter('a[href*=".pdf"], a[href*=".doc"]')->each(function (Crawler $doc) use (&$event) {
                $href = $doc->attr('href');
                $title = trim($doc->text());

                // Get more context from parent if title is too short
                if (strlen($title) < 5) {
                    $parentText = trim($doc->parents()->first()->text());
                    if (strlen($parentText) > 0 && strlen($parentText) < 200) {
                        $title = $parentText;
                    }
                }

                // Classify document type
                $docType = $this->classifyDocumentType($title);

                $event['documents'][] = [
                    'title' => $title,
                    'url' => $this->buildFullUrl($href),
                    'type' => $docType,
                ];
            });

            // Extract committee links
            $event['committees'] = [];
            $descriptionCell->filter('a[href*="structura2015.co"], a[href*="structura.co"]')->each(function (Crawler $committee) use (&$event) {
                $href = $committee->attr('href');
                $name = trim($committee->text());

                // Parse committee ID from URL: idc=2&leg=2024&cam=2
                $queryParams = [];
                parse_str(parse_url($href, PHP_URL_QUERY) ?? '', $queryParams);

                $event['committees'][] = [
                    'name' => $name,
                    'link' => $this->buildFullUrl($href),
                    'committee_id' => $queryParams['idc'] ?? null,
                    'legislature' => $queryParams['leg'] ?? null,
                    'chamber' => $queryParams['cam'] ?? null,
                ];
            });

            // Extract stenogram link
            $stenogramLink = $descriptionCell->filter('a[href*="steno2015.stenograma"], a[href*="steno.stenograma"]')->first();
            if ($stenogramLink->count()) {
                $event['stenogram_link'] = $this->buildFullUrl($stenogramLink->attr('href'));
            }

            // Extract video link (from onclick JavaScript)
            $videoLink = $descriptionCell->filter('a[onclick*="loadintoIframe"]')->first();
            if ($videoLink->count()) {
                $onclick = $videoLink->attr('onclick');
                // Parse: loadintoIframe(1, '/pls/steno/htp_jwplayer?stream=...')
                if (preg_match('/loadintoIframe\(\d+,\s*[\'"]([^\'\"]+)[\'"]/', $onclick, $m)) {
                    $event['video_link'] = $this->buildFullUrl($m[1]);
                }
            }

            $events[] = $event;
        });

        Log::info("CDEP: Extracted {$sequenceOrder} timeline events");

        return $events;
    }

    /**
     * Detect chamber from background color
     */
    protected function detectChamberFromColor($bgColor)
    {
        if (!$bgColor) {
            return null;
        }

        // Normalize color (remove # if present)
        $color = strtolower(str_replace('#', '', $bgColor));

        // Chamber color mapping from CDEP HTML
        $chamberColors = [
            'dfefff' => 'senate',      // Light blue - Senate
            'fff0d8' => 'cdep',        // Light orange/beige - Chamber of Deputies
            'ffffe8' => 'presidential', // Light yellow - Presidential/Parliament
        ];

        return $chamberColors[$color] ?? null;
    }

    /**
     * Classify timeline event type from description
     */
    protected function classifyTimelineEvent($description)
    {
        $description = mb_strtolower($description);

        $patterns = [
            'registered' => ['înregistrat', 'prezentare în biroul permanent'],
            'committee_sent' => ['trimis pentru raport', 'trimis pentru aviz'],
            'committee_report' => ['primire raport', 'primire aviz'],
            'agenda' => ['înscris pe ordinea de zi'],
            'debate' => ['dezbatere în plen'],
            'vote' => ['adoptat', 'respins'],
            'reexamination_request' => ['solicita reexaminarea', 'cerere de reexaminare'],
            'reexamination_vote' => ['ca urmare a cererii de reexaminare'],
            'sent_to_president' => ['trimitere la presedintele româniei'],
            'president_review' => ['depunere la secretarul general'],
            'promulgated' => ['promulgata prin decret'],
            'becomes_law' => ['devine legea'],
            'published' => ['publicare lege în monitorul oficial'],
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($description, $keyword) !== false) {
                    return $type;
                }
            }
        }

        return 'other';
    }

    /**
     * Classify document type from title
     */
    protected function classifyDocumentType($title)
    {
        $title = mb_strtolower($title);

        if (stripos($title, 'expunere de motive') !== false || stripos($title, 'expunerea de motive') !== false) {
            return 'explanatory_memorandum';
        } elseif (stripos($title, 'formă inițiator') !== false || stripos($title, 'forma initiator') !== false) {
            return 'bill_text';
        } elseif (stripos($title, 'forma adoptată') !== false || stripos($title, 'forma adoptata') !== false) {
            return 'adopted_form';
        } elseif (stripos($title, 'raport') !== false) {
            return 'committee_report';
        } elseif (stripos($title, 'aviz') !== false) {
            return 'opinion';
        } elseif (stripos($title, 'amendament') !== false) {
            return 'amendment';
        } elseif (stripos($title, 'stenogram') !== false) {
            return 'stenogram';
        } elseif (stripos($title, 'memorandum') !== false) {
            return 'memorandum';
        } elseif (stripos($title, 'adresa') !== false) {
            return 'official_letter';
        }

        return 'other';
    }

    /**
     * Save bill data to database
     */
    public function saveBill($data, ?ScrapingJob $job = null)
    {
        try {
            // Find existing or create new bill
            $bill = LegislativeBill::where('chamber', $data['chamber'])
                ->where('internal_id', $data['internal_id'])
                ->first();

            $isNew = ! $bill;

            if ($bill) {
                // Detect changes
                if ($bill->hasContentChanged($data)) {
                    $this->recordChanges($bill, $data);
                    $bill->change_count++;
                    $bill->last_changed_at = now();
                }
            } else {
                $bill = new LegislativeBill;
            }

            // Update bill data
            $bill->fill([
                'chamber' => $data['chamber'],
                'internal_id' => $data['internal_id'],
                'bill_number' => $data['bill_number'] ?? null,
                'year' => $data['year'] ?? null,
                'title' => $data['title'] ?? null,
                'type' => $data['type'] ?? null,
                'status' => $data['status'] ?? null,
                'urgency_status' => $data['urgency_status'] ?? false,
                'description' => $data['description'] ?? null,
                'url' => $data['url'] ?? null,
                'content_hash' => $data['content_hash'] ?? null,
                'registration_date' => $data['registration_date'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'last_scraped_at' => now(),
            ]);

            $bill->scrape_count++;
            $bill->save();

            // Save initiators
            if (isset($data['initiators'])) {
                $this->saveInitiators($bill, $data['initiators']);
            }

            // Save documents
            if (isset($data['documents'])) {
                $this->saveDocuments($bill, $data['documents']);
            }

            // Save timeline
            if (isset($data['timeline'])) {
                $this->saveTimeline($bill, $data['timeline']);
            }

            // Update job stats
            if ($job) {
                if ($isNew) {
                    $job->items_created++;
                } else {
                    $job->items_updated++;
                }
                $job->items_processed++;
                $job->save();
            }

            Log::info('CDEP: '.($isNew ? 'Created' : 'Updated')." bill {$bill->internal_id}");

            return $bill;

        } catch (\Exception $e) {
            Log::error('CDEP: Error saving bill: '.$e->getMessage());

            if ($job) {
                $job->items_failed++;
                $job->errors_count++;
                $job->error_log .= "\n[".now().'] '.$e->getMessage();
                $job->save();
            }

            throw $e;
        }
    }

    /**
     * Save initiators for a bill
     */
    protected function saveInitiators(LegislativeBill $bill, array $initiators)
    {
        // Delete existing initiators
        $bill->initiators()->delete();

        // Create new initiators
        foreach ($initiators as $initiator) {
            BillInitiator::create([
                'bill_id' => $bill->id,
                'name' => $initiator['name'],
                'type' => $initiator['type'],
                'role' => $initiator['role'],
                'position' => $initiator['position'] ?? 0,
                'chamber' => $this->chamber,
            ]);
        }
    }

    /**
     * Save documents for a bill
     */
    protected function saveDocuments(LegislativeBill $bill, array $documents)
    {
        foreach ($documents as $doc) {
            // Check if document already exists by URL or hash
            $existing = BillDocument::where('bill_id', $bill->id)
                ->where('url', $doc['url'])
                ->first();

            if (! $existing) {
                BillDocument::create([
                    'bill_id' => $bill->id,
                    'document_type' => $doc['document_type'],
                    'title' => $doc['title'],
                    'url' => $doc['url'],
                    'mime_type' => $doc['mime_type'],
                ]);
            }
        }
    }

    /**
     * Save timeline events for a bill (comprehensive version)
     */
    protected function saveTimeline(LegislativeBill $bill, array $timeline)
    {
        foreach ($timeline as $eventData) {
            // Check if event already exists by sequence_order or date+description
            $existing = BillTimeline::where('bill_id', $bill->id)
                ->where(function ($query) use ($eventData) {
                    $query->where('sequence_order', $eventData['sequence_order'] ?? null)
                        ->orWhere(function ($q) use ($eventData) {
                            $q->where('event_date', $eventData['event_date'] ?? null)
                                ->where('description', $eventData['description'] ?? '');
                        });
                })
                ->first();

            if ($existing) {
                // Update existing event with new data
                $timelineEvent = $existing;
                $timelineEvent->update([
                    'sequence_order' => $eventData['sequence_order'] ?? null,
                    'event_type' => $eventData['event_type'],
                    'chamber_round' => $eventData['chamber_round'] ?? 1,
                    'is_adoption' => $eventData['is_adoption'] ?? false,
                    'is_final' => $eventData['is_final'] ?? false,
                    'vote_result' => $eventData['vote_result'] ?? null,
                    'vote_details' => $eventData['vote_details'] ?? null,
                    'deadline' => $eventData['deadline'] ?? null,
                    'deadline_type' => $eventData['deadline_type'] ?? null,
                    'stenogram_link' => $eventData['stenogram_link'] ?? null,
                    'video_link' => $eventData['video_link'] ?? null,
                    'committees' => $eventData['committees'] ?? [],
                    'documents' => $eventData['documents'] ?? [],
                ]);
            } else {
                // Create new timeline event
                $timelineEvent = BillTimeline::create([
                    'bill_id' => $bill->id,
                    'sequence_order' => $eventData['sequence_order'] ?? null,
                    'event_date' => $eventData['event_date'] ?? null,
                    'event_type' => $eventData['event_type'],
                    'description' => $eventData['description'] ?? '',
                    'chamber' => $eventData['chamber'] ?? $this->chamber,
                    'chamber_round' => $eventData['chamber_round'] ?? 1,
                    'is_adoption' => $eventData['is_adoption'] ?? false,
                    'is_final' => $eventData['is_final'] ?? false,
                    'vote_result' => $eventData['vote_result'] ?? null,
                    'vote_details' => $eventData['vote_details'] ?? null,
                    'deadline' => $eventData['deadline'] ?? null,
                    'deadline_type' => $eventData['deadline_type'] ?? null,
                    'stenogram_link' => $eventData['stenogram_link'] ?? null,
                    'video_link' => $eventData['video_link'] ?? null,
                    'committees' => $eventData['committees'] ?? [],
                    'documents' => $eventData['documents'] ?? [],
                ]);
            }

            // Save committee assignments if present
            if (!empty($eventData['committees'])) {
                foreach ($eventData['committees'] as $committeeData) {
                    // Determine assignment type from event type
                    $assignmentType = 'aviz'; // default
                    if (stripos($eventData['description'], 'raport') !== false) {
                        $assignmentType = 'raport';
                    }

                    // Check if committee assignment already exists
                    $existingCommittee = BillCommittee::where('bill_id', $bill->id)
                        ->where('timeline_event_id', $timelineEvent->id)
                        ->where('committee_name', $committeeData['name'])
                        ->first();

                    if (!$existingCommittee) {
                        BillCommittee::create([
                            'bill_id' => $bill->id,
                            'timeline_event_id' => $timelineEvent->id,
                            'committee_name' => $committeeData['name'],
                            'committee_id' => $committeeData['committee_id'] ?? null,
                            'committee_link' => $committeeData['link'] ?? null,
                            'chamber' => $committeeData['chamber'] ?? $this->chamber,
                            'legislature' => $committeeData['legislature'] ?? null,
                            'assignment_type' => $assignmentType,
                        ]);
                    }
                }
            }

            // Save timeline documents if present
            if (!empty($eventData['documents'])) {
                foreach ($eventData['documents'] as $docData) {
                    // Check if document already exists by URL
                    $existingDoc = BillDocument::where('bill_id', $bill->id)
                        ->where('url', $docData['url'])
                        ->first();

                    if (!$existingDoc) {
                        BillDocument::create([
                            'bill_id' => $bill->id,
                            'timeline_event_id' => $timelineEvent->id,
                            'document_type' => $docData['type'] ?? 'other',
                            'title' => $docData['title'],
                            'url' => $docData['url'],
                            'mime_type' => $this->getMimeTypeFromUrl($docData['url']),
                        ]);
                    } else {
                        // Link existing document to this timeline event if not already linked
                        if (!$existingDoc->timeline_event_id) {
                            $existingDoc->update(['timeline_event_id' => $timelineEvent->id]);
                        }
                    }
                }
            }

            // Extract and save deadlines from eventData to committee assignments
            if (!empty($eventData['deadlines'])) {
                foreach ($eventData['deadlines'] as $deadline) {
                    // Update committee deadlines if this is a committee event
                    if (stripos($eventData['description'], 'trimis pentru') !== false) {
                        BillCommittee::where('bill_id', $bill->id)
                            ->where('timeline_event_id', $timelineEvent->id)
                            ->update([
                                stripos($deadline['type'], 'amendamente') !== false ? 'deadline_amendments' : 'deadline_report' => $deadline['date'],
                            ]);
                    }
                }
            }
        }
    }

    /**
     * Record changes between old and new bill data
     */
    protected function recordChanges(LegislativeBill $bill, array $newData)
    {
        $fields = ['title', 'status', 'type', 'description'];

        foreach ($fields as $field) {
            if (isset($newData[$field]) && $newData[$field] !== $bill->$field) {
                BillChange::create([
                    'bill_id' => $bill->id,
                    'field_name' => $field,
                    'old_value' => $bill->$field,
                    'new_value' => $newData[$field],
                    'change_type' => $this->getChangeType($field),
                    'importance' => $this->getChangeImportance($field),
                    'detected_at' => now(),
                    'detection_method' => 'scraper',
                ]);
            }
        }
    }

    /**
     * Get mime type from URL extension
     */
    protected function getMimeTypeFromUrl($url)
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}

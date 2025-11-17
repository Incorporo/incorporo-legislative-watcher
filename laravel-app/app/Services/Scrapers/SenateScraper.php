<?php

namespace App\Services\Scrapers;

use App\Models\LegislativeBill;
use App\Models\BillDocument;
use App\Models\BillInitiator;
use App\Models\BillTimeline;
use App\Models\BillChange;
use App\Models\ScrapingJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class SenateScraper extends BaseScraper
{
    protected $baseUrl = 'https://www.senat.ro';
    protected $chamber = 'senate';
    protected $delaySeconds = 3;

    /**
     * Scrape list of bills from Senate
     */
    public function scrapeBillList($chamber = null, $year = null, $limit = null)
    {
        $url = "{$this->baseUrl}/legiproiect.aspx";

        Log::info("Senate: Scraping bill list from {$url}");

        try {
            $response = $this->makeRequest($url);
            $crawler = new Crawler($response);

            $bills = [];
            $count = 0;

            // Extract bills from table
            $crawler->filter('table tbody tr')->each(function (Crawler $row) use (&$bills, &$count, $limit, $year) {
                if ($limit && $count >= $limit) {
                    return;
                }

                $cells = $row->filter('td');

                if ($cells->count() >= 4) {
                    // Column 2: Registration number with link
                    $linkCell = $cells->eq(1);
                    $link = $linkCell->filter('a')->first();

                    if ($link->count()) {
                        $href = $link->attr('href');
                        $billNumber = trim($link->text());

                        // Parse the href to get bill ID and details
                        // Format: Legis/Lista.aspx?cod=27167&pos=0&NR=b514&AN=2025
                        if (preg_match('/cod=(\d+)/', $href, $codMatch) &&
                            preg_match('/NR=([^&]+)/', $href, $nrMatch) &&
                            preg_match('/AN=(\d{4})/', $href, $yearMatch)) {

                            $cod = $codMatch[1];
                            $nr = $nrMatch[1];
                            $billYear = (int)$yearMatch[1];

                            // Filter by year if specified
                            if ($year && $billYear != $year) {
                                return;
                            }

                            // Column 4: Description
                            $description = '';
                            if ($cells->count() >= 4) {
                                $description = trim($cells->eq(3)->text());
                            }

                            $bills[] = [
                                'internal_id' => $cod,
                                'bill_number' => $nr,
                                'year' => $billYear,
                                'title' => $description,
                                'url' => $this->buildFullUrl($href),
                            ];

                            $count++;
                        }
                    }
                }
            });

            Log::info("Senate: Found {$count} bills");
            return $bills;

        } catch (\Exception $e) {
            Log::error("Senate: Error scraping bill list: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape detailed information for a specific bill
     */
    public function scrapeBillDetail($cod, $chamber = null)
    {
        // Build URL - we need to find the bill first
        $url = "{$this->baseUrl}/Legis/Lista.aspx?cod={$cod}";

        Log::info("Senate: Scraping bill detail for code {$cod}");

        try {
            $response = $this->makeRequest($url);
            $crawler = new Crawler($response);

            $data = [
                'chamber' => $this->chamber,
                'internal_id' => $cod,
                'url' => $url,
                'metadata' => [],
            ];

            // Extract title from main heading
            $titleNode = $crawler->filter('h3, .headline')->first();
            if ($titleNode->count()) {
                $data['title'] = $this->cleanText($titleNode->text());
            }

            // Extract from detail table
            $crawler->filter('table.legis tr')->each(function (Crawler $row) use (&$data) {
                $cells = $row->filter('td');

                if ($cells->count() >= 2) {
                    $label = trim($cells->eq(0)->text());
                    $value = trim($cells->eq(1)->text());

                    // Extract based on label
                    if (stripos($label, 'Număr înregistrare') !== false) {
                        // Format: B514/2025
                        if (preg_match('/([A-Z]+\d+)\/(\d{4})/', $value, $matches)) {
                            $data['bill_number'] = $matches[1];
                            $data['year'] = (int)$matches[2];
                        }
                    } elseif (stripos($label, 'Stadiu') !== false || stripos($label, 'Status') !== false) {
                        $data['status'] = $value;
                    } elseif (stripos($label, 'Tip inițiativă') !== false) {
                        $data['type'] = $value;
                    } elseif (stripos($label, 'Procedură de urgență') !== false) {
                        $data['urgency_status'] = stripos($value, 'da') !== false;
                    } elseif (stripos($label, 'Cameră decizională') !== false) {
                        $data['decision_chamber'] = $value;
                    } elseif (stripos($label, 'Prima cameră sesizată') !== false) {
                        $data['first_chamber'] = $value;
                    }
                }
            });

            // Extract initiators
            $data['initiators'] = $this->extractInitiators($crawler);

            // Extract timeline events
            $data['timeline'] = $this->extractTimeline($crawler);

            // Extract documents
            $data['documents'] = $this->extractDocuments($crawler);

            // Extract description from content area
            $descNode = $crawler->filter('.legis-content, .description')->first();
            if ($descNode->count()) {
                $data['description'] = $this->cleanText($descNode->text());
            }

            // Parse registration date from timeline
            foreach ($data['timeline'] ?? [] as $event) {
                if ($event['event_type'] === 'registered') {
                    $data['registration_date'] = $event['event_date'];
                    break;
                }
            }

            // Calculate content hash
            $data['content_hash'] = $this->calculateHash($data);
            $data['last_scraped_at'] = now();

            // Fallbacks
            $data['year'] = $data['year'] ?? date('Y');
            $data['bill_number'] = $data['bill_number'] ?? 'S-' . $data['internal_id'];

            return $data;

        } catch (\Exception $e) {
            Log::error("Senate: Error scraping bill {$cod}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract initiators/sponsors
     */
    protected function extractInitiators(Crawler $crawler)
    {
        $initiators = [];

        // Look for initiators section
        $initSection = $crawler->filter('td:contains("Inițiator")')->siblings();

        if ($initSection->count()) {
            $text = $initSection->text();

            // Check for government
            if (stripos($text, 'guvern') !== false) {
                $initiators[] = [
                    'name' => 'Guvernul României',
                    'type' => 'government',
                    'role' => 'primary',
                ];
            }

            // Extract senator/deputy names
            $initSection->filter('a')->each(function (Crawler $node, $i) use (&$initiators) {
                $name = trim($node->text());

                // Skip if it's not a person's name
                if (strlen($name) > 5) {
                    $type = 'mp';

                    // Try to determine chamber from context
                    $href = $node->attr('href');
                    $chamber = null;
                    if (stripos($href, 'senator') !== false) {
                        $chamber = 'senate';
                    } elseif (stripos($href, 'deputat') !== false) {
                        $chamber = 'cdep';
                    }

                    $initiators[] = [
                        'name' => $name,
                        'type' => $type,
                        'role' => $i === 0 ? 'primary' : 'co_sponsor',
                        'position' => $i,
                        'chamber' => $chamber,
                    ];
                }
            });
        }

        return $initiators;
    }

    /**
     * Extract documents
     */
    protected function extractDocuments(Crawler $crawler)
    {
        $documents = [];

        // Find document links
        $crawler->filter('a[href*=".pdf"], a[href*="Legis/PDF"]')->each(function (Crawler $node) use (&$documents) {
            $href = $node->attr('href');
            $title = trim($node->text());

            if (!$title || strlen($title) < 3) {
                // Try to get title from parent or context
                $title = trim($node->parents()->first()->text());
            }

            // Classify document type
            $type = 'other';
            if (stripos($title, 'formă inițiator') !== false || stripos($title, 'propunere legislativă') !== false) {
                $type = 'bill_text';
            } elseif (stripos($title, 'expunere de motive') !== false) {
                $type = 'explanatory_memorandum';
            } elseif (stripos($title, 'raport') !== false) {
                $type = 'committee_report';
            } elseif (stripos($title, 'aviz') !== false || stripos($title, 'opinie') !== false) {
                $type = 'opinion';
            } elseif (stripos($title, 'amendament') !== false) {
                $type = 'amendment';
            }

            $documents[] = [
                'url' => $this->buildFullUrl($href),
                'title' => $title,
                'document_type' => $type,
                'mime_type' => 'application/pdf',
            ];
        });

        return $documents;
    }

    /**
     * Extract timeline events
     */
    protected function extractTimeline(Crawler $crawler)
    {
        $timeline = [];

        // Look for timeline/history table
        $crawler->filter('.timeline table tr, table.istoric tr')->each(function (Crawler $row) use (&$timeline) {
            $cells = $row->filter('td');

            if ($cells->count() >= 2) {
                $dateText = trim($cells->eq(0)->text());
                $eventText = trim($cells->eq(1)->text());

                $date = $this->parseDate($dateText);

                if ($date && strlen($eventText) > 3) {
                    // Check for deadlines
                    $deadline = null;
                    if (preg_match('/termen[:\s]+(\d{1,2}[-.\/ ]\d{1,2}[-.\/ ]\d{4})/i', $eventText, $matches)) {
                        $deadline = $this->parseDate($matches[1]);
                    }

                    $timeline[] = [
                        'event_date' => $date,
                        'description' => $eventText,
                        'event_type' => $this->classifyEvent($eventText),
                        'deadline' => $deadline,
                    ];
                }
            }
        });

        return $timeline;
    }

    /**
     * Save bill data to database (similar to CDEP)
     */
    public function saveBill($data, ScrapingJob $job = null)
    {
        try {
            // Find existing or create new bill
            $bill = LegislativeBill::where('chamber', $data['chamber'])
                ->where('internal_id', $data['internal_id'])
                ->first();

            $isNew = !$bill;

            if ($bill) {
                // Detect changes
                if ($bill->hasContentChanged($data)) {
                    $this->recordChanges($bill, $data);
                    $bill->change_count++;
                    $bill->last_changed_at = now();
                }
            } else {
                $bill = new LegislativeBill();
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
                'first_chamber' => $data['first_chamber'] ?? null,
                'decision_chamber' => $data['decision_chamber'] ?? null,
                'description' => $data['description'] ?? null,
                'url' => $data['url'] ?? null,
                'content_hash' => $data['content_hash'] ?? null,
                'registration_date' => $data['registration_date'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'last_scraped_at' => now(),
            ]);

            $bill->scrape_count++;
            $bill->save();

            // Save related data
            if (isset($data['initiators'])) {
                $this->saveInitiators($bill, $data['initiators']);
            }

            if (isset($data['documents'])) {
                $this->saveDocuments($bill, $data['documents']);
            }

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

            Log::info("Senate: " . ($isNew ? "Created" : "Updated") . " bill {$bill->internal_id}");

            return $bill;

        } catch (\Exception $e) {
            Log::error("Senate: Error saving bill: " . $e->getMessage());

            if ($job) {
                $job->items_failed++;
                $job->errors_count++;
                $job->error_log .= "\n[" . now() . "] " . $e->getMessage();
                $job->save();
            }

            throw $e;
        }
    }

    /**
     * Save initiators (similar to CDEP)
     */
    protected function saveInitiators(LegislativeBill $bill, array $initiators)
    {
        $bill->initiators()->delete();

        foreach ($initiators as $initiator) {
            BillInitiator::create([
                'bill_id' => $bill->id,
                'name' => $initiator['name'],
                'type' => $initiator['type'],
                'role' => $initiator['role'],
                'position' => $initiator['position'] ?? 0,
                'chamber' => $initiator['chamber'] ?? $this->chamber,
            ]);
        }
    }

    /**
     * Save documents
     */
    protected function saveDocuments(LegislativeBill $bill, array $documents)
    {
        foreach ($documents as $doc) {
            $existing = BillDocument::where('bill_id', $bill->id)
                ->where('url', $doc['url'])
                ->first();

            if (!$existing) {
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
     * Save timeline events
     */
    protected function saveTimeline(LegislativeBill $bill, array $timeline)
    {
        foreach ($timeline as $event) {
            $existing = BillTimeline::where('bill_id', $bill->id)
                ->where('event_date', $event['event_date'])
                ->where('description', $event['description'])
                ->first();

            if (!$existing) {
                BillTimeline::create([
                    'bill_id' => $bill->id,
                    'event_date' => $event['event_date'],
                    'event_type' => $event['event_type'],
                    'description' => $event['description'],
                    'deadline' => $event['deadline'] ?? null,
                    'chamber' => $this->chamber,
                ]);
            }
        }
    }

    /**
     * Record changes between versions
     */
    protected function recordChanges(LegislativeBill $bill, array $newData)
    {
        $fields = ['title', 'status', 'type', 'description'];

        foreach ($fields as $field) {
            if (isset($newData[$field]) && $bill->$field !== $newData[$field]) {
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
}

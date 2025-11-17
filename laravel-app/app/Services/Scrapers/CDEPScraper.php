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
use Illuminate\Support\Facades\Storage;
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
     * @param int $chamber Chamber ID (1=Senate, 2=Chamber of Deputies)
     * @param int|null $year Filter by year
     * @param int $limit Maximum bills to scrape
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
            $crawler->filter('a[href*="idp="]')->each(function (Crawler $node) use (&$bills, &$count, $limit, $year) {
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
            Log::error("CDEP: Error scraping bill list: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape detailed information for a specific bill
     *
     * @param string $idp Bill internal ID
     * @param int $chamber Chamber ID
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
            Log::error("CDEP: Error scraping bill {$idp}: " . $e->getMessage());
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
                $data['bill_number'] = $matches[1] . $matches[2];
                $data['year'] = (int)$matches[3];
            } elseif (preg_match('/(\d+)\/(\d{4})/', $numberText, $matches)) {
                $data['bill_number'] = $matches[1];
                $data['year'] = (int)$matches[2];
            }
        }

        // Fallback: try to extract from title
        if (!isset($data['year']) && isset($data['title'])) {
            if (preg_match('/(\d+)\/(\d{4})/', $data['title'], $matches)) {
                $data['bill_number'] = $data['bill_number'] ?? $matches[1];
                $data['year'] = (int)$matches[2];
            }
        }

        // Default to current year if not found
        $data['year'] = $data['year'] ?? date('Y');
        $data['bill_number'] = $data['bill_number'] ?? 'CDEP-' . $data['internal_id'];
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
     * Extract timeline events
     */
    protected function extractTimeline(Crawler $crawler)
    {
        $timeline = [];

        // Look for timeline table
        $crawler->filter('table.simple tr')->each(function (Crawler $row) use (&$timeline) {
            $cells = $row->filter('td');

            if ($cells->count() >= 2) {
                $dateText = trim($cells->eq(0)->text());
                $eventText = trim($cells->eq(1)->text());

                $date = $this->parseDate($dateText);

                if ($date) {
                    $timeline[] = [
                        'event_date' => $date,
                        'description' => $eventText,
                        'event_type' => $this->classifyEvent($eventText),
                    ];
                }
            }
        });

        return $timeline;
    }

    /**
     * Save bill data to database
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

            Log::info("CDEP: " . ($isNew ? "Created" : "Updated") . " bill {$bill->internal_id}");

            return $bill;

        } catch (\Exception $e) {
            Log::error("CDEP: Error saving bill: " . $e->getMessage());

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
     * Save timeline events for a bill
     */
    protected function saveTimeline(LegislativeBill $bill, array $timeline)
    {
        foreach ($timeline as $event) {
            // Check if event already exists
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
                    'chamber' => $this->chamber,
                ]);
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

<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

abstract class BaseScraper
{
    protected $baseUrl;
    protected $chamber;
    protected $userAgent = 'Mozilla/5.0 (compatible; RomanianLegislativeWatcher/1.0; +https://legislative-watcher.ro)';
    protected $delaySeconds = 3; // Default rate limiting
    protected $timeout = 30; // Request timeout in seconds
    protected $maxRetries = 3;
    protected $proxyEnabled = false;
    protected $proxies = [];
    protected $currentProxyIndex = 0;

    public function __construct()
    {
        // Load configuration
        $this->userAgent = config('scraper.user_agent', $this->userAgent);
        $this->delaySeconds = config('scraper.delay_seconds', $this->delaySeconds);
        $this->timeout = config('scraper.timeout_seconds', $this->timeout);
        $this->maxRetries = config('scraper.max_retries', $this->maxRetries);

        // Load proxy configuration
        $this->proxyEnabled = config('scraper.proxy_enabled', false);
        if ($this->proxyEnabled && $proxyConfig = config('scraper.proxy')) {
            // Support multiple proxies separated by comma
            $this->proxies = array_map('trim', explode(',', $proxyConfig));
            Log::info('Proxy enabled with ' . count($this->proxies) . ' proxy(ies)');
        }
    }

    /**
     * Get current proxy for rotation
     */
    protected function getCurrentProxy()
    {
        if (!$this->proxyEnabled || empty($this->proxies)) {
            return null;
        }

        if (!config('scraper.proxy_rotation', true)) {
            // No rotation, always use first proxy
            return $this->proxies[0];
        }

        // Rotate through proxies
        $proxy = $this->proxies[$this->currentProxyIndex];
        $this->currentProxyIndex = ($this->currentProxyIndex + 1) % count($this->proxies);

        return $proxy;
    }

    /**
     * Make HTTP request with rate limiting and error handling
     */
    protected function makeRequest($url, $method = 'GET', $options = [])
    {
        // Rate limiting - wait between requests
        $this->respectRateLimit();

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $httpClient = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'DNT' => '1',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                    'Sec-Fetch-Dest' => 'document',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-Site' => 'none',
                    'Sec-Fetch-User' => '?1',
                ])
                ->timeout($this->timeout);

                // Add proxy if enabled
                $proxy = $this->getCurrentProxy();
                if ($proxy) {
                    $httpClient = $httpClient->withOptions([
                        'proxy' => $proxy,
                        'verify' => false, // Disable SSL verification for proxies
                    ]);
                    Log::debug("Using proxy for {$url}");
                }

                $response = $httpClient->$method($url, $options);

                if ($response->successful()) {
                    Log::debug("Request successful: {$url}");
                    return $response->body();
                }

                // Handle specific HTTP errors
                if ($response->status() === 403) {
                    Log::warning("Access forbidden (403) on {$url} - anti-bot protection detected");
                    if ($proxy) {
                        Log::info("Trying next proxy...");
                    }
                    $attempt++;
                    sleep(5);
                    continue;
                }

                if ($response->status() === 503) {
                    Log::warning("Rate limited (503) on {$url}, waiting...");
                    sleep(10); // Wait longer on rate limit
                    $attempt++;
                    continue;
                }

                if ($response->status() === 404) {
                    Log::warning("Page not found (404): {$url}");
                    throw new \Exception("Page not found: {$url}");
                }

                // Other errors
                throw new \Exception("HTTP {$response->status()} error for {$url}");

            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->maxRetries) {
                    $waitTime = pow(2, $attempt); // Exponential backoff
                    Log::warning("Request failed, retrying in {$waitTime}s... ({$attempt}/{$this->maxRetries}): " . $e->getMessage());
                    sleep($waitTime);
                }
            }
        }

        // All retries failed
        Log::error("All retries failed for {$url}: " . $lastException->getMessage());
        throw $lastException;
    }

    /**
     * Respect rate limiting between requests
     */
    protected function respectRateLimit()
    {
        $cacheKey = "scraper_last_request_{$this->chamber}";
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest) {
            $elapsed = microtime(true) - $lastRequest;
            $waitTime = $this->delaySeconds - $elapsed;

            if ($waitTime > 0) {
                usleep($waitTime * 1000000); // Convert to microseconds
            }
        }

        Cache::put($cacheKey, microtime(true), 60); // Store for 60 seconds
    }

    /**
     * Build full URL from relative path
     */
    protected function buildFullUrl($path)
    {
        if (strpos($path, 'http') === 0) {
            return $path;
        }

        // Handle paths starting with /
        if (strpos($path, '/') === 0) {
            return $this->baseUrl . $path;
        }

        return $this->baseUrl . '/' . $path;
    }

    /**
     * Parse Romanian date format to Y-m-d
     */
    protected function parseDate($dateString)
    {
        if (!$dateString) {
            return null;
        }

        // Clean up the string
        $dateString = trim($dateString);

        // Try standard formats
        $formats = [
            'd.m.Y',      // 01.12.2025
            'd-m-Y',      // 01-12-2025
            'd/m/Y',      // 01/12/2025
            'Y-m-d',      // 2025-12-01
            'd M Y',      // 01 Dec 2025
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // Try to extract date with regex
        if (preg_match('/(\d{1,2})[-.\/ ](\d{1,2})[-.\/ ](\d{4})/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];

            return "{$year}-{$month}-{$day}";
        }

        Log::warning("Could not parse date: {$dateString}");
        return null;
    }

    /**
     * Calculate hash for change detection
     */
    protected function calculateHash($data)
    {
        $relevant = [
            'title' => $data['title'] ?? '',
            'status' => $data['status'] ?? '',
            'type' => $data['type'] ?? '',
            'description' => $data['description'] ?? '',
        ];

        return hash('sha256', json_encode($relevant));
    }

    /**
     * Classify event type from description
     */
    protected function classifyEvent($description)
    {
        $description = strtolower($description);

        $patterns = [
            'registered' => ['înregistrat', 'depus', 'inregistrat'],
            'committee_review' => ['comisie', 'trimis pentru raport'],
            'vote' => ['vot', 'adoptat', 'respins', 'votat'],
            'amended' => ['amendament', 'modificat'],
            'published' => ['publicat', 'monitorul oficial'],
            'opinion' => ['aviz', 'opinie'],
            'debate' => ['dezbatere', 'dezbateri'],
            'promulgated' => ['promulgat'],
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
     * Get change type from field name
     */
    protected function getChangeType($field)
    {
        $types = [
            'status' => 'status',
            'title' => 'content',
            'description' => 'content',
            'type' => 'metadata',
        ];

        return $types[$field] ?? 'metadata';
    }

    /**
     * Get change importance from field name
     */
    protected function getChangeImportance($field)
    {
        $importance = [
            'status' => 'high',
            'title' => 'medium',
            'type' => 'medium',
            'description' => 'low',
        ];

        return $importance[$field] ?? 'low';
    }

    /**
     * Download and store a PDF file
     */
    protected function downloadDocument($url, $billId, $documentType)
    {
        try {
            Log::info("Downloading document: {$url}");

            $httpClient = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])
            ->timeout(60); // Longer timeout for file downloads

            // Add proxy if enabled
            $proxy = $this->getCurrentProxy();
            if ($proxy) {
                $httpClient = $httpClient->withOptions([
                    'proxy' => $proxy,
                    'verify' => false,
                ]);
            }

            $response = $httpClient->get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to download: HTTP {$response->status()}");
            }

            // Generate filename
            $hash = hash('sha256', $url);
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = "bills/{$billId}/{$documentType}_{$hash}.{$extension}";

            // Store file
            \Illuminate\Support\Facades\Storage::put($filename, $response->body());

            return [
                'local_path' => $filename,
                'file_size' => strlen($response->body()),
                'file_hash' => $hash,
            ];

        } catch (\Exception $e) {
            Log::error("Error downloading document {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean HTML text
     */
    protected function cleanText($text)
    {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Normalize party name for consistent tracking
     */
    protected function normalizePartyName($party)
    {
        $party = trim($party);

        $mappings = [
            'PSD' => ['PSD', 'Partidul Social Democrat'],
            'PNL' => ['PNL', 'Partidul Național Liberal'],
            'USR' => ['USR', 'Uniunea Salvați România'],
            'AUR' => ['AUR', 'Alianța pentru Unirea Românilor'],
            'UDMR' => ['UDMR', 'Uniunea Democrată Maghiară din România'],
            'PMP' => ['PMP', 'Partidul Mișcarea Populară'],
        ];

        foreach ($mappings as $normalized => $variants) {
            foreach ($variants as $variant) {
                if (stripos($party, $variant) !== false) {
                    return $normalized;
                }
            }
        }

        return $party;
    }

    /**
     * Abstract methods to be implemented by child classes
     */
    abstract public function scrapeBillList($chamber, $year = null, $limit = null);
    abstract public function scrapeBillDetail($id, $chamber);
    abstract public function saveBill($data, $job = null);
}

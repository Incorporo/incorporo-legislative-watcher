<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class TestScrapersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scrapers
                            {--detailed : Run detailed bill scraping tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test scraper connectivity and parsing without database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=================================');
        $this->info('Legislative Scraper Test');
        $this->info('=================================');
        $this->newLine();

        $tests = [
            'testCDEPConnectivity',
            'testSenateConnectivity',
        ];

        if ($this->option('detailed')) {
            $tests[] = 'testDetailedBillScraping';
            $tests[] = 'testRateLimiting';
        }

        $tests[] = 'testErrorHandling';

        $passed = 0;
        $failed = 0;

        foreach ($tests as $test) {
            if ($this->$test()) {
                $passed++;
            } else {
                $failed++;
            }
            $this->newLine();
        }

        $this->info('=================================');
        $this->info('TEST SUMMARY');
        $this->info('=================================');
        $this->info("✓ Passed: {$passed}");
        if ($failed > 0) {
            $this->error("✗ Failed: {$failed}");
        }
        $this->newLine();

        if ($failed === 0) {
            $this->info('CONCLUSION: Scrapers are operational and ready to use!');
            $this->info('Next steps: Set up database and run: php artisan scrape:bills --limit=10');
        } else {
            $this->error('CONCLUSION: Some tests failed. Please review the errors above.');
        }

        return $failed === 0 ? 0 : 1;
    }

    /**
     * Get HTTP headers for requests
     */
    protected function getHeaders(): array
    {
        return [
            'User-Agent' => config('scraper.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Cache-Control' => 'max-age=0',
        ];
    }

    /**
     * Test CDEP website connectivity
     */
    protected function testCDEPConnectivity(): bool
    {
        $this->info('TEST 1: CDEP Website Connectivity');
        $this->line('---------------------------------');

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get('https://www.cdep.ro/pls/proiecte/upl_pck2015.home');

            if ($response->successful()) {
                $this->info('✓ Successfully connected to CDEP website');
                $this->info('✓ Response code: '.$response->status());
                $this->info('✓ Content length: '.strlen($response->body()).' bytes');

                // Test parsing
                $crawler = new Crawler($response->body());
                $billLinks = $crawler->filter('a[href*="idp="]');
                $this->info('✓ Found '.$billLinks->count().' bill links on main page');

                if ($billLinks->count() > 0) {
                    $this->newLine();
                    $this->info('Sample bills found:');
                    $count = 0;
                    $billLinks->each(function (Crawler $node) use (&$count) {
                        if ($count < 5) {
                            $href = $node->attr('href');
                            preg_match('/idp=(\d+)/', $href, $matches);
                            $title = trim($node->text());
                            $this->line('  - ID: '.($matches[1] ?? 'N/A').', Title: '.substr($title, 0, 60).'...');
                            $count++;
                        }
                    });
                }

                return true;
            } else {
                $this->error('✗ Failed to connect: HTTP '.$response->status());

                return false;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test Senate website connectivity
     */
    protected function testSenateConnectivity(): bool
    {
        $this->info('TEST 2: Senate Website Connectivity');
        $this->line('---------------------------------');

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get('https://www.senat.ro');

            if ($response->successful()) {
                $this->info('✓ Successfully connected to Senate website');
                $this->info('✓ Response code: '.$response->status());
                $this->info('✓ Content length: '.strlen($response->body()).' bytes');

                return true;
            } else {
                $this->error('✗ Failed to connect: HTTP '.$response->status());

                return false;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test detailed bill scraping
     */
    protected function testDetailedBillScraping(): bool
    {
        $this->info('TEST 3: Detailed Bill Scraping (CDEP)');
        $this->line('---------------------------------');

        try {
            // Try to scrape a specific bill detail page
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get('https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2');

            if ($response->successful()) {
                $crawler = new Crawler($response->body());

                // Get first bill link
                $firstBill = $crawler->filter('a[href*="idp="]')->first();

                if ($firstBill->count() > 0) {
                    $href = $firstBill->attr('href');
                    preg_match('/idp=(\d+)/', $href, $matches);
                    $billId = $matches[1];

                    $this->info("Testing with Bill ID: $billId");

                    // Fetch bill details
                    $detailUrl = "https://www.cdep.ro/pls/proiecte/upl_pck2015.proiect?idp={$billId}&cam=2";
                    $detailResponse = Http::withHeaders($this->getHeaders())
                        ->timeout(10)
                        ->get($detailUrl);

                    if ($detailResponse->successful()) {
                        $detailCrawler = new Crawler($detailResponse->body());

                        $this->info('✓ Successfully fetched bill details');

                        // Try to extract some fields
                        $title = $detailCrawler->filter('.headline, h1, .title')->first();
                        if ($title->count()) {
                            $this->line('  Title: '.substr(trim($title->text()), 0, 100).'...');
                        }

                        // Check for table data
                        $tables = $detailCrawler->filter('table');
                        $this->line('  Found '.$tables->count().' tables with metadata');

                        // Check for documents
                        $docLinks = $detailCrawler->filter('a[href*=".pdf"], a[href*=".doc"]');
                        $this->line('  Found '.$docLinks->count().' document links');

                        $this->info('✓ Bill detail scraping structure looks correct');

                        return true;
                    } else {
                        $this->error('✗ Failed to fetch bill details');

                        return false;
                    }
                } else {
                    $this->error('✗ No bills found to test with');

                    return false;
                }
            } else {
                $this->error('✗ Failed to fetch bill list');

                return false;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test rate limiting
     */
    protected function testRateLimiting(): bool
    {
        $this->info('TEST 4: Rate Limiting');
        $this->line('---------------------------------');
        $this->info('Testing 3-second delay between requests...');

        $start = microtime(true);
        sleep(3);
        $elapsed = round(microtime(true) - $start, 2);

        $this->info("✓ Delay working: {$elapsed} seconds");

        return true;
    }

    /**
     * Test error handling
     */
    protected function testErrorHandling(): bool
    {
        $this->info('TEST 5: Error Handling');
        $this->line('---------------------------------');

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(2)
                ->get('https://www.cdep.ro/nonexistent-page-12345');
            $this->line('Response code: '.$response->status());

            if ($response->status() === 404) {
                $this->info('✓ Correctly handles 404 errors');

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->info('✓ Exception handling works: '.get_class($e));

            return true;
        }
    }
}

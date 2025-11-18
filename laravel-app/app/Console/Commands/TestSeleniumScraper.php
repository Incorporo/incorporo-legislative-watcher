<?php

namespace App\Console\Commands;

use App\Services\Scrapers\SeleniumScraper;
use Illuminate\Console\Command;

class TestSeleniumScraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:selenium
                            {url? : URL to test (defaults to CDEP homepage)}
                            {--screenshot= : Save screenshot to path}
                            {--headless : Run in headless mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Selenium WebDriver with Parliament websites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Selenium WebDriver...');
        $this->newLine();

        // Get URL to test
        $url = $this->argument('url') ?? 'https://www.cdep.ro/pls/proiecte/upl_pck2015.home';

        try {
            // Check if ChromeDriver is available
            $chromeDriverPath = '/opt/node22/bin/chromedriver';
            if (! file_exists($chromeDriverPath)) {
                $this->error('ChromeDriver not found at: '.$chromeDriverPath);
                $this->info('Please install ChromeDriver or update the path in config.');

                return 1;
            }

            $this->info("✓ ChromeDriver found: {$chromeDriverPath}");
            $this->newLine();

            // Start ChromeDriver in background
            $this->info('Starting ChromeDriver server...');
            $process = proc_open(
                $chromeDriverPath.' --port=4444',
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if (! $process) {
                $this->error('Failed to start ChromeDriver');

                return 1;
            }

            // Wait for ChromeDriver to start
            sleep(3);

            $this->info('✓ ChromeDriver server started');
            $this->newLine();

            // Create Selenium scraper
            $this->info("Testing URL: {$url}");
            $scraper = new SeleniumScraper;

            // Start browser
            $this->info('Starting Chrome browser...');
            $scraper->start();
            $this->info('✓ Browser started');

            // Load page
            $this->info('Loading page...');
            $html = $scraper->getPageSource($url);

            // Check result
            $htmlLength = strlen($html);
            $this->info('✓ Page loaded successfully!');
            $this->info('  HTML size: '.number_format($htmlLength).' bytes');

            // Show page title
            $title = $scraper->getTitle();
            $this->info("  Page title: {$title}");

            // Show current URL (in case of redirects)
            $currentUrl = $scraper->getCurrentUrl();
            if ($currentUrl !== $url) {
                $this->warn("  Redirected to: {$currentUrl}");
            }

            // Take screenshot if requested
            if ($screenshotPath = $this->option('screenshot')) {
                $this->info('Taking screenshot...');
                if ($scraper->takeScreenshot($screenshotPath)) {
                    $this->info("✓ Screenshot saved: {$screenshotPath}");
                } else {
                    $this->warn('Failed to save screenshot');
                }
            }

            // Show a sample of the HTML
            $this->newLine();
            $this->info('HTML Preview (first 500 characters):');
            $this->line('─────────────────────────────────────');
            $this->line(substr($html, 0, 500).'...');
            $this->line('─────────────────────────────────────');

            // Check for anti-bot indicators
            $this->newLine();
            $this->info('Checking for anti-bot protection...');

            $indicators = [
                'cloudflare' => stripos($html, 'cloudflare') !== false,
                'captcha' => stripos($html, 'captcha') !== false,
                'access denied' => stripos($html, 'access denied') !== false,
                'forbidden' => stripos($html, '403') !== false || stripos($html, 'forbidden') !== false,
            ];

            $blocked = false;
            foreach ($indicators as $name => $found) {
                if ($found) {
                    $this->error("  ✗ Detected: {$name}");
                    $blocked = true;
                } else {
                    $this->info("  ✓ Not detected: {$name}");
                }
            }

            $this->newLine();
            if ($blocked) {
                $this->warn('⚠️  Page may be blocked or protected');
            } else {
                $this->info('✅ No obvious anti-bot protection detected');
            }

            // Clean up
            $scraper->quit();

            // Stop ChromeDriver
            proc_terminate($process);
            proc_close($process);

            $this->newLine();
            $this->info('✅ Test completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('Test failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            // Clean up
            if (isset($process)) {
                proc_terminate($process);
                proc_close($process);
            }

            return 1;
        }
    }
}

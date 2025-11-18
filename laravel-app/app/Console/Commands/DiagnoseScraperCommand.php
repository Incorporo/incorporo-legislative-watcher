<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiagnoseScraperCommand extends Command
{
    protected $signature = 'scrape:diagnose {--test-proxy : Test proxy connectivity}';

    protected $description = 'Diagnose scraper configuration and connectivity issues';

    public function handle()
    {
        $this->info('=== Scraper Diagnostics ===');
        $this->newLine();

        // 1. Configuration Check
        $this->info('1. Configuration Status:');
        $this->displayConfig();
        $this->newLine();

        // 2. Proxy Test
        if ($this->option('test-proxy') || config('scraper.proxy_enabled')) {
            $this->info('2. Proxy Connectivity Test:');
            $this->testProxy();
            $this->newLine();
        }

        // 3. Direct Connectivity Test
        $this->info('3. Direct Connectivity Test:');
        $this->testDirectConnectivity();
        $this->newLine();

        // 4. DNS Resolution
        $this->info('4. DNS Resolution Test:');
        $this->testDNS();
        $this->newLine();

        // 5. Summary
        $this->info('=== Diagnostic Summary ===');
        $this->displaySummary();

        return 0;
    }

    protected function displayConfig()
    {
        $config = [
            ['Setting', 'Value'],
            ['Proxy Enabled', config('scraper.proxy_enabled') ? '✓ YES' : '✗ NO'],
            ['Proxy', config('scraper.proxy') ?: 'NOT SET'],
            ['Proxy Rotation', config('scraper.proxy_rotation') ? 'YES' : 'NO'],
            ['User Agent', substr(config('scraper.user_agent'), 0, 50).'...'],
            ['Delay (seconds)', config('scraper.delay_seconds')],
            ['Timeout (seconds)', config('scraper.timeout_seconds')],
            ['Max Retries', config('scraper.max_retries')],
            ['CDEP Base URL', config('scraper.cdep.base_url')],
            ['Senate Base URL', config('scraper.senate.base_url')],
            ['Selenium Enabled', config('scraper.selenium_enabled') ? 'YES' : 'NO'],
        ];

        $this->table($config[0], array_slice($config, 1));
    }

    protected function testProxy()
    {
        $proxy = config('scraper.proxy');

        if (! $proxy) {
            $this->error('✗ No proxy configured');

            return;
        }

        // Parse proxy URL
        $proxyParts = parse_url($proxy);
        $proxyHost = $proxyParts['host'] ?? 'unknown';

        $this->line("Testing proxy: {$proxy}");
        $this->line("Proxy host: {$proxyHost}");

        // Test 1: DNS Resolution
        $this->line("\nTest 1: DNS Resolution for {$proxyHost}");
        $dnsResult = @gethostbyname($proxyHost);
        if ($dnsResult === $proxyHost) {
            $this->error("✗ DNS resolution failed for {$proxyHost}");
            $this->warn('CRITICAL: Proxy domain cannot be resolved!');
            $this->warn('Possible causes:');
            $this->warn('  - Proxy service is down');
            $this->warn('  - DNS server cannot resolve this domain');
            $this->warn('  - Firewall blocking DNS queries');

            return;
        } else {
            $this->info("✓ DNS resolved to: {$dnsResult}");
        }

        // Test 2: Connectivity through proxy
        $this->line("\nTest 2: HTTP request through proxy");
        try {
            $response = Http::withOptions([
                'proxy' => $proxy,
                'verify' => false,
            ])
                ->timeout(10)
                ->get('https://ipinfo.io/json');

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✓ Proxy connection successful!');
                $this->line('IP: '.($data['ip'] ?? 'unknown'));
                $this->line('Location: '.($data['city'] ?? 'unknown').', '.($data['country'] ?? 'unknown'));
                $this->line('ISP: '.($data['org'] ?? 'unknown'));
            } else {
                $this->error("✗ Proxy request failed with status: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error('✗ Proxy connection failed: '.$e->getMessage());
            $this->warn('Recommended actions:');
            $this->warn('  - Verify proxy credentials');
            $this->warn('  - Check if proxy service is active');
            $this->warn('  - Try a different proxy server');
        }
    }

    protected function testDirectConnectivity()
    {
        $tests = [
            ['CDEP', 'https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2'],
            ['Senate', 'https://www.senat.ro/LegiProiect.aspx'],
        ];

        foreach ($tests as [$name, $url]) {
            $this->line("\nTesting {$name}: {$url}");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('scraper.user_agent'),
                ])
                    ->timeout(15)
                    ->get($url);

                $status = $response->status();
                $size = strlen($response->body());

                if ($response->successful()) {
                    $this->info("✓ {$name} accessible (HTTP {$status}, {$size} bytes)");

                    // Check for anti-bot protection
                    $body = $response->body();
                    $hasCloudflare = stripos($body, 'cloudflare') !== false;
                    $hasRecaptcha = stripos($body, 'recaptcha') !== false;
                    $hasChallenge = stripos($body, 'challenge') !== false;

                    if ($hasCloudflare || $hasRecaptcha || $hasChallenge) {
                        $this->warn("⚠ Anti-bot protection detected:");
                        if ($hasCloudflare) {
                            $this->warn('  - Cloudflare protection');
                        }
                        if ($hasRecaptcha) {
                            $this->warn('  - reCAPTCHA');
                        }
                        if ($hasChallenge) {
                            $this->warn('  - Challenge page');
                        }
                        $this->warn('Recommendation: Use proxy or Selenium');
                    }

                    // Check for actual content
                    if ($name === 'CDEP') {
                        $billsFound = substr_count($body, 'idp=');
                        $this->line("Bills found (idp links): {$billsFound}");
                    } elseif ($name === 'Senate') {
                        $billsFound = substr_count($body, 'cod=');
                        $this->line("Bills found (cod links): {$billsFound}");
                    }

                } else {
                    $this->error("✗ {$name} returned HTTP {$status}");

                    if ($status === 403) {
                        $this->warn('Access Forbidden - likely anti-bot protection');
                    } elseif ($status === 503) {
                        $this->warn('Service Unavailable - server may be down or rate limiting');
                    }
                }
            } catch (\Exception $e) {
                $this->error("✗ {$name} connection failed: ".$e->getMessage());
            }
        }
    }

    protected function testDNS()
    {
        $hosts = [
            'cdep.ro',
            'senat.ro',
        ];

        if ($proxyHost = parse_url(config('scraper.proxy'), PHP_URL_HOST)) {
            $hosts[] = $proxyHost;
        }

        foreach ($hosts as $host) {
            $ip = @gethostbyname($host);
            if ($ip === $host) {
                $this->error("✗ Failed to resolve: {$host}");
            } else {
                $this->info("✓ {$host} → {$ip}");
            }
        }
    }

    protected function displaySummary()
    {
        $proxyEnabled = config('scraper.proxy_enabled');
        $proxySet = ! empty(config('scraper.proxy'));

        $issues = [];
        $recommendations = [];

        // Check for common issues
        if ($proxyEnabled && ! $proxySet) {
            $issues[] = 'Proxy enabled but not configured';
            $recommendations[] = 'Set SCRAPER_PROXY in .env file';
        }

        if ($proxyEnabled && $proxySet) {
            $proxyHost = parse_url(config('scraper.proxy'), PHP_URL_HOST);
            $dnsTest = @gethostbyname($proxyHost);
            if ($dnsTest === $proxyHost) {
                $issues[] = 'Proxy DNS resolution failure';
                $recommendations[] = 'Check proxy service status or disable proxy';
            }
        }

        if (! empty($issues)) {
            $this->newLine();
            $this->error('Issues Found:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
        }

        if (! empty($recommendations)) {
            $this->newLine();
            $this->info('Recommendations:');
            foreach ($recommendations as $rec) {
                $this->line("  • {$rec}");
            }
        }

        if (empty($issues)) {
            $this->newLine();
            $this->info('✓ No configuration issues detected');
        }

        $this->newLine();
        $this->info('Next Steps:');
        $this->line('  1. Review any errors or warnings above');
        $this->line('  2. Update .env configuration as needed');
        $this->line('  3. Run: php artisan config:clear');
        $this->line('  4. Test scraping: php artisan scrape:bills --limit=5');
    }
}

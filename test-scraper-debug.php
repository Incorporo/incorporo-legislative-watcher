<?php

/**
 * Debug script to test bill scraper functionality
 * This will help identify why we're getting 0 bills
 */

// Load Laravel autoloader and bootstrap
require __DIR__ . '/laravel-app/vendor/autoload.php';

$app = require_once __DIR__ . '/laravel-app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

echo "=== BILL SCRAPER DEBUG TEST ===\n\n";

// Test configuration
$proxy = getenv('SCRAPER_PROXY');
$proxyEnabled = getenv('SCRAPER_PROXY_ENABLED');

echo "Environment:\n";
echo "- SCRAPER_PROXY: " . ($proxy ?: 'NOT SET') . "\n";
echo "- SCRAPER_PROXY_ENABLED: " . ($proxyEnabled ?: 'NOT SET') . "\n\n";

// Test 1: CDEP Website
echo "=== TEST 1: CDEP (Chamber of Deputies) ===\n";
$cdepUrl = 'https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2';
echo "URL: {$cdepUrl}\n";

try {
    $httpClient = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'ro-RO,ro;q=0.9,en;q=0.8',
    ])->timeout(30);

    // Add proxy if configured
    if ($proxy) {
        echo "Using proxy: {$proxy}\n";
        $httpClient = $httpClient->withOptions([
            'proxy' => $proxy,
            'verify' => false,
        ]);
    }

    echo "Making request...\n";
    $response = $httpClient->get($cdepUrl);

    echo "Status: " . $response->status() . "\n";
    echo "Body length: " . strlen($response->body()) . " bytes\n";

    if ($response->successful()) {
        $crawler = new Crawler($response->body());

        // Check for bills
        $billLinks = $crawler->filter('a[href*="idp="]');
        echo "Bills found (a[href*=\"idp=\"]): " . $billLinks->count() . "\n";

        // Show first few bills if found
        if ($billLinks->count() > 0) {
            echo "\nFirst 5 bills:\n";
            $count = 0;
            $billLinks->each(function (Crawler $node) use (&$count) {
                if ($count < 5) {
                    $href = $node->attr('href');
                    $text = trim($node->text());
                    preg_match('/idp=(\d+)/', $href, $matches);
                    $idp = $matches[1] ?? 'N/A';
                    echo "  - ID: {$idp}, Title: " . substr($text, 0, 60) . "...\n";
                    $count++;
                }
            });
        }

        // Debug: show page structure
        echo "\nPage structure debug:\n";
        echo "- Tables: " . $crawler->filter('table')->count() . "\n";
        echo "- Links total: " . $crawler->filter('a')->count() . "\n";
        echo "- Forms: " . $crawler->filter('form')->count() . "\n";

        // Save HTML for inspection if needed
        file_put_contents('/tmp/cdep_response.html', $response->body());
        echo "Saved response to: /tmp/cdep_response.html\n";

    } else {
        echo "ERROR: HTTP " . $response->status() . "\n";
        echo "Body preview: " . substr($response->body(), 0, 500) . "\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 2: Senate Website
echo "=== TEST 2: Senate ===\n";
$senateUrl = 'https://www.senat.ro/legis/lista.aspx';
echo "URL: {$senateUrl}\n";

try {
    $httpClient = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'ro-RO,ro;q=0.9,en;q=0.8',
    ])->timeout(30);

    // Add proxy if configured
    if ($proxy) {
        echo "Using proxy: {$proxy}\n";
        $httpClient = $httpClient->withOptions([
            'proxy' => $proxy,
            'verify' => false,
        ]);
    }

    echo "Making request...\n";
    $response = $httpClient->get($senateUrl);

    echo "Status: " . $response->status() . "\n";
    echo "Body length: " . strlen($response->body()) . " bytes\n";

    if ($response->successful()) {
        $crawler = new Crawler($response->body());

        // Check for bills in tables
        $tableRows = $crawler->filter('table tbody tr');
        echo "Table rows found: " . $tableRows->count() . "\n";

        // Count rows with bill links
        $billCount = 0;
        $tableRows->each(function (Crawler $row) use (&$billCount) {
            $cells = $row->filter('td');
            if ($cells->count() >= 4) {
                $link = $cells->eq(1)->filter('a')->first();
                if ($link->count() && stripos($link->attr('href'), 'cod=') !== false) {
                    $billCount++;
                }
            }
        });

        echo "Bills found: {$billCount}\n";

        // Debug: show page structure
        echo "\nPage structure debug:\n";
        echo "- Tables: " . $crawler->filter('table')->count() . "\n";
        echo "- Links total: " . $crawler->filter('a')->count() . "\n";

        // Save HTML for inspection
        file_put_contents('/tmp/senate_response.html', $response->body());
        echo "Saved response to: /tmp/senate_response.html\n";

    } else {
        echo "ERROR: HTTP " . $response->status() . "\n";
        echo "Body preview: " . substr($response->body(), 0, 500) . "\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END DEBUG TEST ===\n";

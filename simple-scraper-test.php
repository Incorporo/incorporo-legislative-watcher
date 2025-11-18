<?php

/**
 * Simple scraper test without Laravel dependencies
 * Tests basic HTTP connectivity to Parliament websites
 */

echo "=== SIMPLE SCRAPER TEST ===\n\n";

$proxy = getenv('SCRAPER_PROXY');
echo "Proxy: " . ($proxy ?: 'NONE') . "\n\n";

// Test 1: CDEP with curl
echo "=== TEST 1: CDEP (Chamber of Deputies) ===\n";
$cdepUrl = 'https://www.cdep.ro/pls/proiecte/upl_pck2015.home?cam=2';
echo "URL: {$cdepUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cdepUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: ro-RO,ro;q=0.9,en;q=0.8',
    'Connection: keep-alive',
]);

if ($proxy) {
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    echo "Using proxy: {$proxy}\n";
}

echo "Making request...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response length: " . strlen($response) . " bytes\n";

if ($error) {
    echo "CURL Error: {$error}\n";
}

if ($httpCode == 200 && $response) {
    // Count occurrences of 'idp=' in links
    preg_match_all('/href="[^"]*idp=(\d+)[^"]*"/', $response, $matches);
    $billCount = count($matches[1]);
    echo "Bills found (idp links): {$billCount}\n";

    if ($billCount > 0) {
        echo "\nFirst 5 bill IDs:\n";
        for ($i = 0; $i < min(5, count($matches[1])); $i++) {
            echo "  - " . $matches[1][$i] . "\n";
        }
    } else {
        // Check what we actually got
        echo "\nResponse preview (first 1000 chars):\n";
        echo substr($response, 0, 1000) . "\n...\n";

        // Check for common elements
        $hasHtml = stripos($response, '<html') !== false;
        $hasBody = stripos($response, '<body') !== false;
        $hasTable = stripos($response, '<table') !== false;
        $hasLinks = stripos($response, '<a ') !== false;

        echo "\nHTML structure check:\n";
        echo "- Has <html>: " . ($hasHtml ? 'YES' : 'NO') . "\n";
        echo "- Has <body>: " . ($hasBody ? 'YES' : 'NO') . "\n";
        echo "- Has <table>: " . ($hasTable ? 'YES' : 'NO') . "\n";
        echo "- Has <a>: " . ($hasLinks ? 'YES' : 'NO') . "\n";

        // Check for anti-bot protection
        $hasCloudflare = stripos($response, 'cloudflare') !== false || stripos($response, 'cf-browser-verification') !== false;
        $hasRecaptcha = stripos($response, 'recaptcha') !== false;
        $hasCaptcha = stripos($response, 'captcha') !== false;

        echo "\nBot protection check:\n";
        echo "- Cloudflare: " . ($hasCloudflare ? 'YES' : 'NO') . "\n";
        echo "- reCAPTCHA: " . ($hasRecaptcha ? 'YES' : 'NO') . "\n";
        echo "- CAPTCHA: " . ($hasCaptcha ? 'YES' : 'NO') . "\n";
    }

    // Save for inspection
    file_put_contents('/tmp/cdep_simple_test.html', $response);
    echo "Saved to: /tmp/cdep_simple_test.html\n";
} else {
    echo "Failed to fetch page\n";
}

echo "\n";

// Test 2: Senate
echo "=== TEST 2: Senate ===\n";
$senateUrl = 'https://www.senat.ro/legis/lista.aspx';
echo "URL: {$senateUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $senateUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language: ro-RO,ro;q=0.9,en;q=0.8',
    'Connection: keep-alive',
]);

if ($proxy) {
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    echo "Using proxy: {$proxy}\n";
}

echo "Making request...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response length: " . strlen($response) . " bytes\n";

if ($error) {
    echo "CURL Error: {$error}\n";
}

if ($httpCode == 200 && $response) {
    // Count bill links
    preg_match_all('/href="[^"]*cod=(\d+)[^"]*"/', $response, $matches);
    $billCount = count($matches[1]);
    echo "Bills found (cod links): {$billCount}\n";

    if ($billCount > 0) {
        echo "\nFirst 5 bill codes:\n";
        for ($i = 0; $i < min(5, count($matches[1])); $i++) {
            echo "  - " . $matches[1][$i] . "\n";
        }
    } else {
        // Check what we got
        echo "\nResponse preview (first 1000 chars):\n";
        echo substr($response, 0, 1000) . "\n...\n";

        // Check for anti-bot protection
        $hasCloudflare = stripos($response, 'cloudflare') !== false;
        $hasRecaptcha = stripos($response, 'recaptcha') !== false;
        $hasCaptcha = stripos($response, 'captcha') !== false;

        echo "\nBot protection check:\n";
        echo "- Cloudflare: " . ($hasCloudflare ? 'YES' : 'NO') . "\n";
        echo "- reCAPTCHA: " . ($hasRecaptcha ? 'YES' : 'NO') . "\n";
        echo "- CAPTCHA: " . ($hasCaptcha ? 'YES' : 'NO') . "\n";
    }

    // Save for inspection
    file_put_contents('/tmp/senate_simple_test.html', $response);
    echo "Saved to: /tmp/senate_simple_test.html\n";
} else {
    echo "Failed to fetch page\n";
}

echo "\n=== END TEST ===\n";

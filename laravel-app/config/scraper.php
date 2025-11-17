<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scraper Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for legislative bill scrapers
    |
    */

    'user_agent' => env('SCRAPER_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),

    'delay_seconds' => env('SCRAPER_DELAY_SECONDS', 3),

    'timeout_seconds' => env('SCRAPER_TIMEOUT_SECONDS', 30),

    'max_retries' => env('SCRAPER_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Proxy Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP/HTTPS proxy settings for bypassing anti-bot protection.
    | Supports residential and datacenter proxies.
    |
    | Format: http://username:password@host:port
    | Example: http://user:pass@proxy.example.com:8080
    |
    | You can also provide multiple proxies (comma-separated) for rotation:
    | SCRAPER_PROXY="http://proxy1.com:8080,http://proxy2.com:8080"
    |
    */

    'proxy_enabled' => env('SCRAPER_PROXY_ENABLED', false),

    'proxy' => env('SCRAPER_PROXY', null),

    // If true, will rotate through multiple proxies if provided
    'proxy_rotation' => env('SCRAPER_PROXY_ROTATION', true),

    /*
    |--------------------------------------------------------------------------
    | Selenium WebDriver Configuration
    |--------------------------------------------------------------------------
    |
    | Selenium is used to bypass anti-bot protection by controlling a real
    | browser instance. This is more effective than HTTP requests for sites
    | with strict bot detection (like Parliament websites).
    |
    */

    'selenium_enabled' => env('SELENIUM_ENABLED', false),

    'selenium_url' => env('SELENIUM_URL', 'http://localhost:4444'),

    'selenium_headless' => env('SELENIUM_HEADLESS', true),

    'cdep' => [
        'base_url' => env('CDEP_BASE_URL', 'https://www.cdep.ro'),
    ],

    'senate' => [
        'base_url' => env('SENATE_BASE_URL', 'https://www.senat.ro'),
    ],

];

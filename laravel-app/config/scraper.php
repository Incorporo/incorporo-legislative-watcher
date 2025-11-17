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

    'cdep' => [
        'base_url' => env('CDEP_BASE_URL', 'https://www.cdep.ro'),
    ],

    'senate' => [
        'base_url' => env('SENATE_BASE_URL', 'https://www.senat.ro'),
    ],

];

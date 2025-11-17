<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as OpenRouter AI, notification services, etc.
    |
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3.5-sonnet'),
        'max_tokens' => env('OPENROUTER_MAX_TOKENS', 4096),
        'temperature' => env('OPENROUTER_TEMPERATURE', 0.7),
    ],

];

<?php

declare(strict_types=1);

return [
    'name'            => 'Railgun',
    'env'             => env('APP_ENV', 'master'),
    'debug'           => env('APP_DEBUG', false),
    'url'             => env('APP_URL', 'http://localhost'),
    'timezone'        => env('APP_TIMEZONE', 'UTC'),
    'locale'          => env('APP_LOCALE', 'fr'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key'             => env('APP_KEY'),
    'cipher'          => 'AES-256-CBC',
];

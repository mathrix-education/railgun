<?php

declare(strict_types=1);

return [
    'name'            => 'Railgun',
    'release'         => env('APP_RELEASE', 'RELEASE_ALIAS'),
    'env'             => env('APP_ENV', 'production'),
    'debug'           => env('APP_DEBUG', env('APP_ENV', 'production') !== 'production'),
    'url'             => env('APP_URL', 'http://localhost'),
    'timezone'        => env('APP_TIMEZONE', 'UTC'),
    'locale'          => env('APP_LOCALE', 'fr'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key'             => env('APP_KEY'),
    'cipher'          => 'AES-256-CBC',
];

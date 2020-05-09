<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores'  => [
        'array' => ['driver' => 'array'],
        'file'  => [
            'driver'   => 'file',
            'path'     => storage_path('framework/cache/data'),
            'fallback' => 'array',
        ],
        'redis' => [
            'driver'     => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
            'fallback'   => 'file',
        ],
    ],
    'prefix'  => env('CACHE_PREFIX', ''),
];

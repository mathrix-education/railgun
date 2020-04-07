<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores'  => [
        'array' => ['driver' => 'array'],
        'file'  => [
            'driver' => 'file',
            'path'   => storage_path('framework/cache/data'),
        ],
        'redis' => [
            'driver'     => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
        ],
    ],
    'prefix'  => env('CACHE_PREFIX', ''),
];

<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'database'),
    'stores'  => [
        'array'    => ['driver' => 'array'],
        'database' => [
            'driver'     => 'database',
            'table'      => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => env('CACHE_DATABASE_CONNECTION', null),
        ],
        'redis'    => [
            'driver'     => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'default'),
        ],
    ],
    'prefix'  => env('CACHE_PREFIX', ''),
];

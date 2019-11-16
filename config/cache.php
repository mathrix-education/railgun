<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'database'),
    'stores'  => [
        'database' => [
            'driver'     => 'database',
            'table'      => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => env('CACHE_DATABASE_CONNECTION', null),
        ],
    ],
    'prefix'  => env('CACHE_PREFIX', ''),
];

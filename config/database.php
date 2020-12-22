<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'   => env('DB_PREFIX', ''),
        ],

        'mysql' => [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', 3306),
            'database'    => env('DB_DATABASE', 'forge'),
            'username'    => env('DB_USERNAME', 'forge'),
            'password'    => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset'     => env('DB_CHARSET', 'utf8mb4'),
            'collation'   => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix'      => env('DB_PREFIX', ''),
            'strict'      => env('DB_STRICT_MODE', true),
            'engine'      => env('DB_ENGINE', null),
            'timezone'    => env('DB_TIMEZONE', '+00:00'),
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client'     => env('REDIS_CLIENT', 'phpredis'),
        'cluster'    => env('REDIS_CLUSTER', false),
        'default'    => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => (int)env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'prefix'   => prefix('', true),
        ],
        'cache'      => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => (int)env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 0),
            'prefix'   => prefix('cache', true),
        ],
        'prometheus' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => (int)env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 0),
            'prefix'   => prefix('prometheus', true),
        ],
    ],
];

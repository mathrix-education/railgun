<?php

declare(strict_types=1);

return [
    'default'  => env('LOG_CHANNEL', 'daily'),
    'channels' => [
        'daily'       => [
            'driver' => 'daily',
            'path'   => storage_path('logs/daily.log'),
            'level'  => 'debug',
            'days'   => 7,
        ],
        'stdout'      => [
            'driver' => 'single',
            'path'   => 'php://stdout',
            'level'  => 'debug',
        ],
    ],
];

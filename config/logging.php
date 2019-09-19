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
        'stackdriver' => [
            'driver'    => 'monolog',
            'handler'   => Monolog\Handler\RotatingFileHandler::class,
            'with'      => ['filename' => '/var/log/lumen.log'], // Cloud Run logs location
            'formatter' => App\Logging\StackdriverFormatter::class,
        ],
    ],
];

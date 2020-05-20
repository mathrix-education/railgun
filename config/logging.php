<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Railgun\Logging\GoogleCloudJsonFormatter;

return [
    'default'  => env('LOG_CHANNEL', 'stdout'),
    'channels' => [
        'daily'        => [
            'driver' => 'daily',
            'path'   => storage_path('logs/daily.log'),
            'level'  => 'debug',
            'days'   => 7,
        ],
        'stdout'       => [
            'driver' => 'single',
            'path'   => 'php://stdout',
            'level'  => 'debug',
        ],
        'google-cloud' => [
            'driver'       => 'monolog',
            'handler'      => StreamHandler::class,
            'handler_with' => ['stream' => 'php://stderr'],
            'formatter'    => GoogleCloudJsonFormatter::class,
            'level'        => 'debug',
        ],
    ],
];

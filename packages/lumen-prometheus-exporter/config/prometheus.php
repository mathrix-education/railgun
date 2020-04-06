<?php

declare(strict_types=1);

return [
    'namespace'  => env('APP_NAME') . '_' . env('APP_ENV'),
    'adapter'    => env('PROMETHEUS_ADAPTER'),
    'action'     => 'Mathrix\\Prometheus\\Controllers\\PrometheusController@metrics',
    'redis'      => [
        // 'connection' => env('PROMETHEUS_REDIS_CONNECTION', 'prometheus'),
        'host'     => env('PROMETHEUS_REDIS_HOST', '127.0.0.1'),
        'port'     => env('PROMETHEUS_REDIS_PORT', 6379),
        'password' => env('PROMETHEUS_REDIS_PASSWORD', null),
        'database' => env('PROMETHEUS_REDIS_DB', 2),
    ],
    'collectors' => [
        // Register your collectors here
        // 'Mathrix\\Prometheus\\Collectors\\HttpCollector'
        // 'Mathrix\\Prometheus\\Collectors\\QueueCollector'
    ],
];

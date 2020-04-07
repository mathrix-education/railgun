<?php

declare(strict_types=1);

return [
    'dsn'         => env('SENTRY_DSN'),
    'environment' => env('APP_ENV'),
    'release'     => env('APP_RELEASE', 'RELEASE_ALIAS'),
    'breadcrumbs' => [
        'logs'         => false,
        'queue_info'   => true,
        'sql_queries'  => true,
        'sql_bindings' => true,
    ],
];

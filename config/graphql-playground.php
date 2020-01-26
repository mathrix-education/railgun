<?php

declare(strict_types=1);

return [
    'route'    => [
        'uri'  => '/',
        'name' => 'graphql-playground',
    ],
    'endpoint' => '/',
    'enabled'  => env('GRAPHQL_PLAYGROUND_ENABLED', env('APP_ENV') !== 'master'),
];

<?php

declare(strict_types=1);

if (app()->environment() === 'master') {
    $origins = ['https://mathrix.fr', 'https://*.mathrix.fr'];
} else {
    $origins = ['https://*.mathrix.fr', 'http://localhost:4200'];
}

return [
    'paths'                    => ['*'],
    'supports_credentials'     => false,
    'allowed_origins'          => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'allowed_methods'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
];

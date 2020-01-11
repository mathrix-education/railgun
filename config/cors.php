<?php

declare(strict_types=1);

if (app()->environment() === 'master') {
    $origins = ['https://mathrix.fr', 'https://*.mathrix.fr'];
} else {
    $origins = ['https://*.mathrix.fr', 'http://localhost:4200'];
}

return [
    'paths'                    => ['*'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => false,
    'max_age'                  => 0,
    'supports_credentials'     => true,
];

<?php

declare(strict_types=1);

return [
    'default'  => 'primary',
    'accounts' => [
        'primary'   => [
            'client'   => [
                'api_key' => env('STRIPE_PRIMARY_API_KEY'),
            ],
            'webhooks' => [
                'secret' => env('STRIPE_PRIMARY_WEBHOOKS_SECRET'),
            ],
        ],
        'secondary' => [
            'client'   => [
                'api_key' => env('STRIPE_SECONDARY_API_KEY'),
            ],
            'webhooks' => [
                'secret' => env('STRIPE_PRIMARY_WEBHOOKS_SECRET'),
            ],
        ],
    ],
];

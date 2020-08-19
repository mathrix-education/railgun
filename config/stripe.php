<?php

declare(strict_types=1);

return [
    'defaults'       => [
        'client'   => 'primary',
        'webhooks' => 'primary',
    ],
    'clients'        => [
        'primary'   => env('STRIPE_PRIMARY_API_KEY'),
        'secondary' => env('STRIPE_SECONDARY_API_KEY'),
    ],
    'webhooks'       => [
        'primary'   => env('STRIPE_PRIMARY_WEBHOOKS_SECRET'),
        'secondary' => env('STRIPE_PRIMARY_WEBHOOKS_SECRET'),
    ],
    'webhooks_route' => [
        'uri'  => '/webhooks/stripe/{account}',
        'name' => 'stripe_webhooks',
    ],
];

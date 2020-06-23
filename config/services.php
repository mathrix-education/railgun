<?php

declare(strict_types=1);

return [
    'mailjet' => [
        'key'     => env('MAILJET_KEY'),
        'secret'  => env('MAILJET_SECRET'),
        'call'    => env('MAILJET_CALL', true),
        'options' => ['version' => 'v3.1'],
    ],
    'stripe'  => [
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'route'     => env('STRIPE_WEBOOKS_ROUTE', '/webhooks/stripe'),
            'secret'    => env('STRIPE_WEBHOOKS_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOKS_TOLERANCE', 300),
        ],
    ],
];

<?php

declare(strict_types=1);

return [
    'mailjet'   => [
        'key'     => env('MAILJET_KEY'),
        'secret'  => env('MAILJET_SECRET'),
        'call'    => false,
        'options' => ['version' => 'v3.1'],
    ],
    'stripe'    => [
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'route'     => env('STRIPE_WEBOOKS_ROUTE', '/webhooks/stripe'),
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'braintree' => [
        'environment' => env('BRAINTREE_ENVIRONMENT', 'sandbox'),
        'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
        'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
        'privateKey'  => env('BRAINTREE_PRIVATE_KEY'),
    ],
];

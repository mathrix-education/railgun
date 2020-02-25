<?php

declare(strict_types=1);

return [
    'mailgun'   => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
    'stripe'    => [
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'braintree' => [
        'environment' => env('BRAINTREE_ENVIRONMENT', \Braintree\Gateway::class),
        'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
        'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
        'privateKey'  => env('BRAINTREE_PRIVATE_KEY'),
    ],
];

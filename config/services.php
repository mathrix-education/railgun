<?php

declare(strict_types=1);

return [
    'mailjet'   => [
        'key'     => env('MAILJET_KEY'),
        'secret'  => env('MAILJET_SECRET'),
        'call'    => env('MAILJET_CALL', true),
        'options' => ['version' => 'v3.1'],
    ],
    'braintree' => [
        'environment' => env('BRAINTREE_ENVIRONMENT', 'sandbox'),
        'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
        'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
        'privateKey'  => env('BRAINTREE_PRIVATE_KEY'),
    ],
];

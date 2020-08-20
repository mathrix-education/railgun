<?php

declare(strict_types=1);

use Railgun\Billing\Braintree\BraintreeGatewayRegistry;

return [
    'defaults'       => [
        'gateway' => BraintreeGatewayRegistry::BRAINTREE_GATEWAY_LEGACY,
    ],
    'gateways'       => [
        BraintreeGatewayRegistry::BRAINTREE_GATEWAY_LEGACY => [
            'environment' => env('BRAINTREE_ENVIRONMENT', 'sandbox'),
            'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
            'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey'  => env('BRAINTREE_PRIVATE_KEY'),
        ],
    ],
    'webhooks_route' => [
        'uri'  => '/webhooks/braintree/{gateway}',
        'name' => 'braintree_webhooks',
    ],
];

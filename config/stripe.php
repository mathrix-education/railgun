<?php

declare(strict_types=1);

use Railgun\Billing\Stripe\StripeClientRegistry;

return [
    'defaults'       => [
        'client'   => StripeClientRegistry::CLIENT_MAIN,
        'webhooks' => StripeClientRegistry::CLIENT_MAIN,
    ],
    'clients'        => [
        StripeClientRegistry::CLIENT_MAIN   => env('STRIPE_API_KEY'),
        StripeClientRegistry::CLIENT_LEGACY => env('STRIPE_LEGACY_API_KEY'),
    ],
    'webhooks'       => [
        StripeClientRegistry::CLIENT_MAIN   => env('STRIPE_WEBHOOKS_SECRET'),
        StripeClientRegistry::CLIENT_LEGACY => env('STRIPE_LEGACY_WEBHOOKS_SECRET'),
    ],
    'webhooks_route' => [
        'uri'  => '/webhooks/stripe/{account}',
        'name' => 'stripe_webhooks',
    ],
];

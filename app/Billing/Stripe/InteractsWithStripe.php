<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Stripe\StripeClient;
use function app;

trait InteractsWithStripe
{
    protected StripeClient $client;

    public function initializeInteractsWithStripe(): void
    {
        $this->client = app()->make(StripeClient::class);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

/**
 *
 */
class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use function env;

class StripeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
}

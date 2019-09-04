<?php

namespace App\Services\Stripe;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class StripeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));
    }
}

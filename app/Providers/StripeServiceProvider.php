<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

/**
 * Class StripeServiceProvider
 * Setup Stripe PHP SDK.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class StripeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));
    }
}

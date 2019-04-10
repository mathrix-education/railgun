<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

/**
 * Class StripeServiceProvider.
 * Setup Stripe PHP SDK.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class StripeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));
    }
}

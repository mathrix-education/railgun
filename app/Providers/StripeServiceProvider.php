<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Stripe\Stripe;

/**
 * Class StripeServiceProvider
 * Setup Stripe PHP SDK
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Providers
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class StripeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Stripe::setApiVersion(env("STRIPE_API_VERSION"));
        Stripe::setApiKey(env("STRIPE_SECRET"));
        Cashier::useCurrency("eur");
    }
}

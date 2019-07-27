<?php

namespace App\Services\Braintree;

use App\Observers\LineItemObserver;
use App\Services\Braintree\Models\LineItem;
use Braintree\Gateway;
use Illuminate\Support\ServiceProvider;
use UnexpectedValueException;

/**
 * Class BraintreeGatewayServiceProvider.
 * Provides Braintree_Gateway to the Laravel Container.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class BraintreeGatewayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $braintreeEnv = env("BRAINTREE_ENVIRONMENT", "sandbox");

        if ($this->app->environment() === "testing" && $braintreeEnv === "production") {
            throw new UnexpectedValueException("Braintree should not be used in production while testing!");
        }

        $this->app->singleton(Gateway::class, function () {
            return new Gateway([
                "environment" => env("BRAINTREE_ENVIRONMENT", "sandbox"),
                "merchantId" => env("BRAINTREE_MERCHANT_ID"),
                "publicKey" => env("BRAINTREE_PUBLIC_KEY"),
                "privateKey" => env("BRAINTREE_PRIVATE_KEY")
            ]);
        });

        LineItem::observe(LineItemObserver::class);
    }


    public function register(): void
    {
    }
}

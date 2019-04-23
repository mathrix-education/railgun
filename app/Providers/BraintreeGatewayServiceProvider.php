<?php

namespace App\Providers;

use Braintree\Configuration;
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

        Configuration::environment(env("BRAINTREE_ENVIRONMENT", "sandbox"));
        Configuration::merchantId(env("BRAINTREE_MERCHANT_ID"));
        Configuration::publicKey(env("BRAINTREE_PUBLIC_KEY"));
        Configuration::privateKey(env("BRAINTREE_PRIVATE_KEY"));
    }
}

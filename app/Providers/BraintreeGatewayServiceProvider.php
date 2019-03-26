<?php

namespace App\Providers;

use Braintree\Configuration;
use Illuminate\Support\ServiceProvider;

/**
 * Class BraintreeGatewayServiceProvider.
 * Provides Braintree_Gateway to the Laravel Container.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Providers
 */
class BraintreeGatewayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Configuration::environment(env("BRAINTREE_ENVIRONMENT", "sandbox"));
        Configuration::merchantId(env("BRAINTREE_MERCHANT_ID"));
        Configuration::publicKey(env("BRAINTREE_PUBLIC_KEY"));
        Configuration::privateKey(env("BRAINTREE_PRIVATE_KEY"));
    }
}

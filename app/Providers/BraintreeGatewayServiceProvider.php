<?php
namespace App\Providers;

use App\Exceptions\Http400InvalidRequestException;
use Braintree_Configuration;
use Braintree_Gateway;
use Illuminate\Support\ServiceProvider;

/**
 * Class BraintreeGatewayServiceProvider
 * Provides Braintree_Gateway to the Laravel Container
 *
 * @author Mathrix Back-End Team
 * @package App\Providers
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class BraintreeGatewayServiceProvider extends ServiceProvider
{
    /**
     * @throws Http400InvalidRequestException
     */
    public function boot()
    {
        $environment = env("BRAINTREE_ENVIRONMENT");

        if (!in_array($environment, ["sandbox", "production"])) {
            throw new Http400InvalidRequestException("`$environment` is not a valid Braintree environment");
        }

        Braintree_Configuration::environment($environment);
        Braintree_Configuration::merchantId(env("BRAINTREE_MERCHANT_ID"));
        Braintree_Configuration::publicKey(env("BRAINTREE_PUBLIC_KEY"));
        Braintree_Configuration::privateKey(env("BRAINTREE_PRIVATE_KEY"));
    }
}

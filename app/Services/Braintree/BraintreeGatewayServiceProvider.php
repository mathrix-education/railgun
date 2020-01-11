<?php

declare(strict_types=1);

namespace App\Services\Braintree;

use Braintree\Gateway;
use Illuminate\Support\ServiceProvider;
use UnexpectedValueException;
use function env;

/**
 * Provides Braintree_Gateway to the Laravel Container.
 */
class BraintreeGatewayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $braintreeEnv = env('BRAINTREE_ENVIRONMENT', 'sandbox');

        if ($braintreeEnv === 'production' && $this->app->environment() === 'testing') {
            throw new UnexpectedValueException('Braintree should not be used in production while testing!');
        }

        $this->app->singleton(Gateway::class, static function () {
            return new Gateway([
                'environment' => env('BRAINTREE_ENVIRONMENT', 'sandbox'),
                'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
                'privateKey' => env('BRAINTREE_PRIVATE_KEY'),
            ]);
        });
    }
}

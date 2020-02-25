<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Braintree\Gateway;
use Illuminate\Support\ServiceProvider;

/**
 *
 */
class BraintreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Gateway::class, static function () {
            return new Gateway([
                'environment' => config('services.braintree.environment'),
                'merchantId'  => config('services.braintree.merchantId'),
                'publicKey'   => config('services.braintree.publicKey'),
                'privateKey'  => config('services.braintree.privateKey'),
            ]);
        });
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Braintree\Gateway;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use function config;

class BraintreeServiceProvider extends ServiceProvider implements DeferrableProvider
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

    public function provides(): array
    {
        return [Gateway::class];
    }
}

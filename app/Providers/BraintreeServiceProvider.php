<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Braintree\Gateway;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\Billing\Webhooks\BraintreeWebhooksController;
use function config;

/**
 * @property-read Application $app
 */
class BraintreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Gateway::class, static function () {
            return new Gateway([
                'environment' => config('braintree.environment'),
                'merchantId'  => config('braintree.merchant_id'),
                'publicKey'   => config('braintree.public_key'),
                'privateKey'  => config('braintree.private_key'),
            ]);
        });
    }

    public function boot(): void
    {
        $this->app->router->post('/webhooks/braintree', BraintreeWebhooksController::class . '@handle');
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Billing\Braintree;

use Braintree\Gateway;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

/**
 * @property-read Application $app
 */
class BraintreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Gateway::class, function () {
            return new Gateway($this->app['config']['braintree']);
        });
    }

    public function boot(): void
    {
        $this->app->configure('braintree');
        $this->app->router->post('/webhooks/braintree', BraintreeWebhooksController::class . '@handle');
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Psr\Log\LoggerInterface;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * @property-read Application $app
 */
class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClientManager::class);
        $this->app->alias(StripeClientManager::class, 'stripe');

        $this->app->singleton(StripeClient::class, function () {
            return $this->app->make(StripeClientManager::class)->client();
        });
        $this->app->alias(StripeClient::class, 'stripe.instance');
    }

    /**
     */
    public function boot(): void
    {
        Stripe::setLogger($this->app->make(LoggerInterface::class));
    }
}

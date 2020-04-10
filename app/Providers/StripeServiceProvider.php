<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Config\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Railgun\Billing\Webhooks\StripeWebhooksController;
use Stripe\Stripe;
use function config;

/**
 * Set {@see Stripe} API key.
 */
class StripeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Repository $config */
        $config = $this->app->make('config');
        // Register the Stripe API Key
        $key = $config->get('services.stripe.secret');

        if (Str::startsWith($key, 'sk_test')) {
            $config->set('services.stripe.sandbox', true);
        }

        Stripe::setApiKey($key);
        Stripe::setLogger($this->app->make(LoggerInterface::class));

        // Register the webhooks route
        /** @var Router $router */
        $router = $this->app->make('router');
        $router->post(
            config('services.stripe.webhook.route'),
            StripeWebhooksController::class . '@handle',
        );
    }
}

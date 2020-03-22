<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Railgun\Billing\Webhooks\StripeWebhooksController;
use Stripe\Stripe;
use function config;

class StripeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->app->make('router');
        $router->get('/webhooks/stripe', StripeWebhooksController::class . '@handle');
        Stripe::setApiKey(config('services.stripe.secret'));
    }
}

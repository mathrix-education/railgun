<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Psr\Log\LoggerInterface;
use Railgun\Billing\Stripe\Commands\SetupWebhookCommand;
use Railgun\Billing\Stripe\Commands\SyncProductsCommand;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * @property-read Application $app
 */
class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClientRegistry::class);

        $this->app->singleton(StripeClient::class, function () {
            return $this->app->make(StripeClientRegistry::class)->make();
        });

        $this->app->singleton(StripeWebhookRegistry::class);

        $this->app->singleton(StripeWebhook::class, function () {
            return $this->app->make(StripeWebhookRegistry::class)->make();
        });

        $this->app->singleton(ModeMachine::class);
    }

    public function boot(): void
    {
        Stripe::setLogger($this->app->make(LoggerInterface::class));

        // Register commands
        $this->commands([
            SetupWebhookCommand::class,
            SyncProductsCommand::class,
        ]);

        /** @var Repository $config */
        $config = $this->app->make('config');

        // Register webhooks route
        $this->app->router->post(
            $config->get('stripe.webhooks_route.uri'),
            [
                'as'   => $config->get('stripe.webhooks_route.name'),
                'uses' => StripeWebhooksController::class,
            ],
        );
    }
}

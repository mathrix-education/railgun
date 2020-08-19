<?php

declare(strict_types=1);

namespace Railgun\Billing\Braintree;

use Braintree\Gateway;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\Billing\Braintree\Webhooks\WebhooksController;

/**
 * @property-read Application $app
 */
class BraintreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BraintreeGatewayRegistry::class);
        $this->app->singleton(Gateway::class, function () {
            return $this->app->make(BraintreeGatewayRegistry::class)->make();
        });
    }

    public function boot(): void
    {
        $this->app->configure('braintree');

        /** @var Repository $config */
        $config = $this->app->make('config');

        // Register webhooks route
        $this->app->router->post(
            $config->get('braintree.webhooks_route.uri'),
            [
                'as'   => $config->get('braintree.webhooks_route.name'),
                'uses' => WebhooksController::class,
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace Mathrix\Healthz;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Routing\Router;
use function dirname;

/**
 * Registers the {@see ChecksRegistry} and the check route based in the healthz configuration.
 */
class HealthzServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/healthz.php', 'healthz');

        $this->app->singleton(ChecksRegistry::class);

        /** @var Repository $config */
        $config = $this->app['config'];

        /** @var Router $router */
        $router = $this->app['router'];

        $router->get($config->get('healthz.url'), $config->get('healthz.action'));
    }
}

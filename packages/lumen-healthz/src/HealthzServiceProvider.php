<?php

declare(strict_types=1);

namespace Mathrix\Healthz;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Routing\Router;

class HealthzServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/healthz.php', 'healthz');
        /** @var Repository $config */
        $config = $this->app['config'];

        $this->app->singleton(ChecksCollection::class, static function () use ($config) {
            return new ChecksCollection($config->get('healthz.checks'));
        });

        /** @var Router $router */
        $router = $this->app['router'];

        $router->get($config->get('healthz.url'), $config->get('healthz.action'));
    }
}

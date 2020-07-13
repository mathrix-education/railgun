<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\Actions\SitemapAction;
use Railgun\Actions\VersionAction;

/**
 * @property-read Application $app
 */
class RouterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->router->get('/', VersionAction::class);
        $this->app->router->get('sitemap', SitemapAction::class);
    }
}

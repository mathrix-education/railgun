<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Mathrix\Lumen\JWT\Auth\JWTAuthServiceProvider;
use MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Tracing\TracingServiceProvider;
use Railgun\Console\Kernel;
use Railgun\Exceptions\Handler;
use Railgun\MigrationV7\MigrationV7ServiceProvider;
use Railgun\Models\Comment;
use Railgun\Models\Notion;
use Railgun\Models\Post;
use Railgun\Models\User;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;

/**
 * Register all application service providers.
 *
 * @property-read Application $app
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('path.config', $this->app->configPath());

        // Singletons
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        $this->app->singleton(KernelContract::class, Kernel::class);

        // Vendor service providers
        $this->app->register(SentryServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(JWTAuthServiceProvider::class);
        $this->app->register(MigrationV7ServiceProvider::class);
        $this->app->register(LighthouseServiceProvider::class);
        $this->app->register(TracingServiceProvider::class);
        $this->app->register(GraphQLPlaygroundServiceProvider::class);
        $this->app->register(RedisServiceProvider::class);

        // MorphMap
        Relation::morphMap([
            // Models
            'notion'  => Notion::class,
            'post'    => Post::class,
            'comment' => Comment::class,
            'user'    => User::class,
        ]);
    }
}

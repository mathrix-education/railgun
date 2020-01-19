<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Kernel;
use App\Exceptions\Handler;
use App\MigrationV7\MigrationV7ServiceProvider;
use App\Models\Comment;
use App\Models\Notion;
use App\Models\Post;
use App\Models\User;
use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Mathrix\Lumen\JWT\Auth\JWTAuthServiceProvider;
use MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Tracing\TracingServiceProvider;

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

        // Middleware
        $this->app->middleware([HandleCors::class]);

        $this->app->register(\Sentry\Laravel\ServiceProvider::class);

        // Vendor service providers
        $this->app->register(CorsServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(FacebookServiceProvider::class);
        $this->app->register(GoogleCloudStorageServiceProvider::class);

        // Mathrix Lumen Zero Providers
        $this->app->register(JWTAuthServiceProvider::class);
        $this->app->register(MigrationV7ServiceProvider::class);

        $this->app->register(LighthouseServiceProvider::class);
        $this->app->register(TracingServiceProvider::class);
        $this->app->register(GraphQLPlaygroundServiceProvider::class);

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

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Kernel;
use App\Exceptions\Handler;
use App\Middleware\AsMiddleware;
use App\MigrationV7\MigrationV7ServiceProvider;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\Braintree\BraintreeGatewayServiceProvider;
use App\Services\Stripe\StripeServiceProvider;
use Fruitcake\Cors\CorsServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Mathrix\Lumen\JWT\Auth\JWTAuthServiceProvider;
use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Providers\ZeroServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;

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
        $this->app->middleware([HandleCors::class, AsMiddleware::class]);

        $this->app->register(\Sentry\Laravel\ServiceProvider::class);
        $this->app->register(BraintreeGatewayServiceProvider::class);

        // Vendor service providers
        $this->app->register(CorsServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(MailServiceProvider::class);
        $this->app->register(JWTAuthServiceProvider::class);
        $this->app->register(FacebookServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);
        $this->app->register(GoogleCloudStorageServiceProvider::class);

        // Mathrix Lumen Zero Providers
        $this->app->register(ZeroServiceProvider::class);

        $this->app->register(MigrationV7ServiceProvider::class);

        $this->app->register(LighthouseServiceProvider::class);

        ScopesLoader::registerAdditionalScopes(['users:as']);

        // MorphMap
        Relation::morphMap([
            // Models
            'activity' => Activity::class,
            'notion'   => Notion::class,
            'module'   => Module::class,
            'post'     => Post::class,
            'comment'  => Comment::class,
            'user'     => User::class,
            'product'  => Product::class,
        ]);
    }
}

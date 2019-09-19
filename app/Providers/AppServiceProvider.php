<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Discount;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\Braintree\BraintreeGatewayServiceProvider;
use App\Services\Stripe\StripeServiceProvider;
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;
use Mathrix\Lumen\JWT\Auth\JWTServiceProvider;
use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Zero\Providers\ObserverServiceProvider;
use Mathrix\Lumen\Zero\Providers\PolicyServiceProvider;
use Mathrix\Lumen\Zero\Providers\RegistrarServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;
use const DIRECTORY_SEPARATOR;
use function app;
use function env;

/**
 * Register all application service providers.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('SENTRY_DSN') !== null) {
            $this->app->register(SentryServiceProvider::class);
        }

        $this->app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
        $this->app->instance('path.public', app()->basePath() . DIRECTORY_SEPARATOR . 'public');
        $this->app->instance('cache.store', app()->basePath() . DIRECTORY_SEPARATOR . 'public');
        // App service providers
        $this->app->register(BraintreeGatewayServiceProvider::class);

        // Vendor service providers
        $this->app->register(CorsServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(MailServiceProvider::class);
        $this->app->register(JWTServiceProvider::class);
        $this->app->register(FacebookServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);
        $this->app->register(GoogleCloudStorageServiceProvider::class);

        // Mathrix Lumen Zero Providers
        $this->app->register(ObserverServiceProvider::class);
        $this->app->register(PolicyServiceProvider::class);
        $this->app->register(RegistrarServiceProvider::class);

        ScopesLoader::registerAdditionalScopes(['users:as']);

        // MorphMap
        Relation::morphMap([
            // Models
            'activity' => Activity::class,
            'notion' => Notion::class,
            'module' => Module::class,
            'post' => Post::class,
            'comment' => Comment::class,
            'user' => User::class,
            'product' => Product::class,
            'discount' => Discount::class,
        ]);
    }
}

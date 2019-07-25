<?php

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
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;
use Carbon\Carbon;
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
use Superbalist\LaravelGoogleCloudStorage\GoogleCloudStorageServiceProvider;

/**
 * Class AppServiceProvider.
 * Register all application service providers.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // App service providers
        $this->app->register(BraintreeGatewayServiceProvider::class);


        // Vendor service providers
        $this->app->register(CorsServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(MailServiceProvider::class);
        // $this->app->register(SitemapServiceProvider::class);
        $this->app->register(GoogleCloudStorageServiceProvider::class);
        $this->app->register(JWTServiceProvider::class);
        $this->app->register(FacebookServiceProvider::class);

        // Mathrix Lumen Zero Providers
        $this->app->register(ObserverServiceProvider::class);
        $this->app->register(PolicyServiceProvider::class);
        $this->app->register(RegistrarServiceProvider::class);

        if (env("SENTRY_DSN") !== null) {
            $this->app->register(SentryServiceProvider::class);
        }

        ScopesLoader::registerAdditionalScopes(["users:as"]);


        Carbon::setToStringFormat("c");

        // MorphMap
        Relation::morphMap([
            // Models
            "activity" => Activity::class,
            "notion" => Notion::class,
            "module" => Module::class,
            "post" => Post::class,
            "comment" => Comment::class,
            "user" => User::class,
            "product" => Product::class,
            "discount" => Discount::class
        ]);
    }
}

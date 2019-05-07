<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentAnswerNotification;
use App\Observers\BaseObserver;
use App\Observers\NotificationObserver;
use App\Policies\BasePolicy;
use App\Policies\FilePolicy;
use App\Policies\NotificationPolicy;
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravelium\Sitemap\SitemapServiceProvider;
use Mathrix\Lumen\JWT\Auth\JWTServiceProvider;
use Mathrix\Lumen\JWT\Auth\ScopesLoader;
use Mathrix\Lumen\Providers\ObserverServiceProvider;
use Mathrix\Lumen\Providers\PolicyServiceProvider;
use Mathrix\Lumen\Providers\RegistrarServiceProvider;
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
        $this->app->register(EventServiceProvider::class);
        $this->app->register(BraintreeGatewayServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);

        // Vendor service providers
        $this->app->register(CorsServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(MailServiceProvider::class);
        $this->app->register(NotificationServiceProvider::class);
        $this->app->register(SitemapServiceProvider::class);
        $this->app->register(GoogleCloudStorageServiceProvider::class);
        $this->app->register(JWTServiceProvider::class);

        // Mathrix Lumen Zero Providers
        $this->registerObservers();
        $this->registerPolicies();
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

            // Notifications
            "comment_answer" => CommentAnswerNotification::class
        ]);
    }


    /**
     * Register observers.
     */
    private function registerObservers(): void
    {
        ObserverServiceProvider::$IgnoredObservers = [
            BaseObserver::class,
            NotificationObserver::class
        ];

        $this->app->register(ObserverServiceProvider::class);

        DatabaseNotification::observe(NotificationObserver::class);
    }


    /**
     * Register policies.
     */
    private function registerPolicies(): void
    {
        PolicyServiceProvider::$IgnoredPolicies = [
            BasePolicy::class,
            NotificationPolicy::class,
            FilePolicy::class,
        ];

        $this->app->register(PolicyServiceProvider::class);

        Gate::policy(FilePolicy::class, FilePolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
    }
}

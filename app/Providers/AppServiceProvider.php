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
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Mathrix\Lumen\Providers\ObserverServiceProvider;
use Mathrix\Lumen\Providers\PolicyServiceProvider;

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
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(BraintreeGatewayServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);

        // Vendor service providers
        $this->app->register(\Backup\BackupServiceProvider::class);
        $this->app->register(\Barryvdh\Cors\ServiceProvider::class);
        $this->app->register(\Dusterio\LumenPassport\PassportServiceProvider::class);
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\Mail\MailServiceProvider::class);
        $this->app->register(\Illuminate\Notifications\NotificationServiceProvider::class);
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->app->register(\Laravelium\Sitemap\SitemapServiceProvider::class);

        // Mathrix Zero Providers
        $this->registerObservers();
        $this->registerPolicies();
        $this->app->register(\Mathrix\Lumen\Providers\RegistrarServiceProvider::class);

        if (app()->environment("local")) {
            $this->app->register(\Vluzrmos\Tinker\TinkerServiceProvider::class);
        }

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

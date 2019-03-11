<?php
namespace App\Providers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentAnswerNotification;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider.
 * Register all application service providers.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Providers
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
        $this->app->register(NewsletterServiceProvider::class);
        $this->app->register(ObserverServiceProvider::class);
        $this->app->register(PolicyServiceProvider::class);
        $this->app->register(BraintreeGatewayServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);

        // Vendor service providers
        $this->app->register(\Backup\BackupServiceProvider::class);
        $this->app->register(\Barryvdh\Cors\ServiceProvider::class);
        $this->app->register(\Dusterio\LumenPassport\PassportServiceProvider::class);
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\Mail\MailServiceProvider::class);
        $this->app->register(\Illuminate\Notifications\NotificationServiceProvider::class);
        $this->app->register(\Illuminate\Redis\RedisServiceProvider::class);
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->app->register(\Laravelium\Sitemap\SitemapServiceProvider::class);
        $this->app->register(\Appzcoder\LumenRoutesList\RoutesCommandServiceProvider::class);

        $this->app->register(GoogleDriveServiceProvider::class);

        if (app()->environment("dev", "local")) {
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
}

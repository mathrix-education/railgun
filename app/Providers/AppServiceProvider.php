<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Laravel\Tinker\TinkerServiceProvider;
use Mathrix\Lumen\JWT\JWTServiceProvider;
use Mathrix\Mailjet\MailjetServiceProvider;
use MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Tracing\TracingServiceProvider;
use Railgun\Console\Kernel;
use Railgun\Exceptions\Handler;
use Railgun\Handlers\ExceptionConverter;
use Railgun\Middleware\ImpersonateMiddleware;
use Railgun\MigrationV7\MigrationV7ServiceProvider;
use Railgun\Models\Comment;
use Railgun\Models\Notion;
use Railgun\Models\Pack;
use Railgun\Models\Post;
use Railgun\Models\Training;
use Railgun\Models\User;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;
use function in_array;

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
        $this->app->singleton(ExceptionConverter::class, ExceptionConverter::class);
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        $this->app->singleton(KernelContract::class, Kernel::class);

        // Vendor service providers
        $this->app->register(SentryServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(JWTServiceProvider::class);
        $this->app->register(MigrationV7ServiceProvider::class);
        $this->app->register(LighthouseServiceProvider::class);
        $this->app->register(TracingServiceProvider::class);
        $this->app->register(GraphQLPlaygroundServiceProvider::class);
        $this->app->register(RedisServiceProvider::class);
        $this->app->register(GoogleServiceProvider::class);
        $this->app->register(FacebookServiceProvider::class);
        $this->app->register(StripeServiceProvider::class);
        $this->app->register(BraintreeServiceProvider::class);
        $this->app->register(TinkerServiceProvider::class);
        $this->app->register(MailServiceProvider::class);
        $this->app->register(MailjetServiceProvider::class);
        $this->app->register(CryptoServiceProvider::class);
    }

    public function boot(): void
    {
        // Headers
        $this->app->middleware([ImpersonateMiddleware::class]);

        Gate::define('scope', static function (?User $user, string $scope) {
            return $user !== null && in_array($scope, $user->scopes);
        });

        // MorphMap
        Relation::morphMap([
            // Models
            'notion'   => Notion::class,
            'pack'     => Pack::class,
            'training' => Training::class,
            'post'     => Post::class,
            'comment'  => Comment::class,
            'user'     => User::class,
        ]);
    }
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Laravel\Tinker\TinkerServiceProvider;
use Mathrix\Lumen\JWT\JWTServiceProvider;
use Mathrix\Mailjet\MailjetServiceProvider;
use MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Tracing\TracingServiceProvider;
use Railgun\MigrationV7\MigrationV7ServiceProvider;
use Railgun\Providers\AppServiceProvider;
use Railgun\Providers\BraintreeServiceProvider;
use Railgun\Providers\CryptoServiceProvider;
use Railgun\Providers\EventServiceProvider;
use Railgun\Providers\FacebookServiceProvider;
use Railgun\Providers\GoogleServiceProvider;
use Railgun\Providers\StripeServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;

(new LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Application(dirname(__DIR__));
$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('auth');
$app->configure('cache');
$app->configure('database');
$app->configure('filesystems');
$app->configure('graphql-playground');
$app->configure('jwt');
$app->configure('lighthouse');
$app->configure('logging');
$app->configure('mail');
$app->configure('queue');
$app->configure('sentry');
$app->configure('services');
$app->configure('urls');

// System
$app->register(AppServiceProvider::class);
$app->register(EventServiceProvider::class);
$app->register(FilesystemServiceProvider::class);
$app->register(RedisServiceProvider::class);
$app->register(NotificationServiceProvider::class);

// GraphQL
$app->register(LighthouseServiceProvider::class);
$app->register(TracingServiceProvider::class);
$app->register(GraphQLPlaygroundServiceProvider::class);

// Authentication
$app->register(JWTServiceProvider::class);
$app->register(GoogleServiceProvider::class);
$app->register(FacebookServiceProvider::class);
$app->register(CryptoServiceProvider::class);

// Mails
$app->register(MailServiceProvider::class);
$app->register(MailjetServiceProvider::class);

$app->register(StripeServiceProvider::class);
$app->register(BraintreeServiceProvider::class);

// Sentry
if ($app->environment(['master', 'dev', 'v7']) && !$app->runningUnitTests()) {
    $app->register(SentryServiceProvider::class);
}

// Debug
if ($app->runningInConsole()) {
    $app->register(MigrationV7ServiceProvider::class);
}
if ($app->runningInConsole() && !$app->runningUnitTests()) {
    $app->register(TinkerServiceProvider::class);
}

return $app;

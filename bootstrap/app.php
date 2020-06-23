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
use Mathrix\Healthz\HealthzServiceProvider;
use Mathrix\Prometheus\PrometheusServiceProvider;
use MLL\GraphQLPlayground\GraphQLPlaygroundServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Tracing\TracingServiceProvider;
use Railgun\Actions\SitemapAction;
use Railgun\Providers\AppServiceProvider;
use Railgun\Providers\AuthServiceProvider;
use Railgun\Providers\BraintreeServiceProvider;
use Railgun\Providers\CryptoServiceProvider;
use Railgun\Providers\FacebookServiceProvider;
use Railgun\Providers\GoogleServiceProvider;
use Railgun\Providers\StripeServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryServiceProvider;
use Superbalist\LaravelGoogleCloudStorage\GoogleCloudStorageServiceProvider;
use Windy\Guardian\GuardianServiceProvider;
use Windy\LaravelCacheFallback\CacheFallbackServiceProvider;
use Windy\Mailjet\MailjetServiceProvider;

(new LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Application(dirname(__DIR__));
$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('auth');
$app->configure('braintree');
$app->configure('cache');
$app->configure('database');
$app->configure('filesystems');
$app->configure('guardian');
$app->configure('graphql-playground');
$app->configure('lighthouse');
$app->configure('logging');
$app->configure('mail');
$app->configure('queue');
$app->configure('sentry');
$app->configure('services');
$app->configure('urls');

// System
$app->register(AppServiceProvider::class);
$app->register(AuthServiceProvider::class);
$app->register(FilesystemServiceProvider::class);
$app->register(RedisServiceProvider::class);
$app->register(NotificationServiceProvider::class);
$app->register(CacheFallbackServiceProvider::class);

// GraphQL
$app->register(LighthouseServiceProvider::class);
if ($app->environment(['stage', 'edge']) && !$app->runningUnitTests()) {
    $app->register(TracingServiceProvider::class);
}
$app->register(GraphQLPlaygroundServiceProvider::class);

// Authentication
$app->register(GuardianServiceProvider::class);
$app->register(GoogleServiceProvider::class);
$app->register(FacebookServiceProvider::class);
$app->register(CryptoServiceProvider::class);

// Mails
$app->register(MailServiceProvider::class);
$app->register(MailjetServiceProvider::class);

// Google Cloud
$serviceAccount = storage_path('keychain/service_account.json');
if (file_exists($serviceAccount)) {
    putenv("GOOGLE_APPLICATION_CREDENTIALS=$serviceAccount");
}
$app->register(GoogleServiceProvider::class);
$app->register(GoogleCloudStorageServiceProvider::class);

// Billing
$app->register(StripeServiceProvider::class);
$app->register(BraintreeServiceProvider::class);

// Sentry
if ($app->environment(['stable', 'edge']) && !$app->runningUnitTests()) {
    $app->register(SentryServiceProvider::class);
}

// Prometheus
$app->configure('prometheus');
$app->register(PrometheusServiceProvider::class);

// Healthz
$app->register(HealthzServiceProvider::class);
$app->configure('healthz');

// Debug
if ($app->runningInConsole() && class_exists(TinkerServiceProvider::class)) {
    $app->register(TinkerServiceProvider::class);
}

// Routes
$app->router->get('sitemap', SitemapAction::class);

return $app;

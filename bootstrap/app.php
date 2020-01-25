<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Providers\AppServiceProvider;
use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;

(new LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Application(dirname(__DIR__));
$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('auth');
$app->configure('cache');
$app->configure('cors');
$app->configure('filesystems');
$app->configure('graphql-playground');
$app->configure('jwt_auth');
$app->configure('lighthouse');
$app->configure('logging');
$app->configure('mail');
$app->configure('queue');
$app->configure('sentry');
$app->configure('services');

$app->register(AppServiceProvider::class);

return $app;

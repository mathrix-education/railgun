<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Railgun\Providers\AppServiceProvider;
use Railgun\Providers\EventServiceProvider;

(new LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Application(dirname(__DIR__));
$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('auth');
$app->configure('cache');
$app->configure('cors');
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

$app->register(AppServiceProvider::class);
$app->register(EventServiceProvider::class);

return $app;

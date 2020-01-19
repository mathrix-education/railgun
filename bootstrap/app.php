<?php

declare(strict_types=1);

use App\Services\GoogleCloud\KMS\SetupEnvironment;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new SetupEnvironment(dirname(__DIR__)))->bootstrap();
} catch (ApiException | ValidationException $e) {
}

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

$app = new Laravel\Lumen\Application(dirname(__DIR__));
$app->withFacades();
$app->withEloquent();

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

$app->register(App\Providers\AppServiceProvider::class);

return $app;

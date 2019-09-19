<?php

declare(strict_types=1);

use App\Middleware\AsMiddleware;
use App\Providers\AppServiceProvider;
use Barryvdh\Cors\HandleCors;
use Illuminate\Contracts\Mail\Mailer;

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We"ll use this
| application as an "IoC" container and router for this framework.
|
*/
$app = new Laravel\Lumen\Application(realpath(__DIR__ . '/../'));
$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    static function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);

/*
 * Fix the sitemap issue
 *
 * @see https://stackoverflow.com/questions/37945911/lumen-cache-store-is-not-instantiable
 */
$app->singleton(
    Illuminate\Cache\Repository::class,
    Illuminate\Contracts\Cache\Repository::class
);

$app->alias('mailer', Mailer::class);

/*
|--------------------------------------------------------------------------
| External configurations
|--------------------------------------------------------------------------
|
| Load configuration files from /config/
|
*/

$app->configure('auth');
$app->configure('cors');
$app->configure('filesystems');
$app->configure('logging');
$app->configure('mail');
$app->configure('queue');
$app->configure('services');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that"ll be assigned to some specific routes.
|
*/

$app->middleware([
    HandleCors::class,
    AsMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application"s service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(AppServiceProvider::class);

return $app;

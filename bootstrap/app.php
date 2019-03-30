<?php
require_once __DIR__ . "/../vendor/autoload.php";

// Generate environment variables
// Load DotEnv
try {
    (new Dotenv\Dotenv(__DIR__ . "/../"))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
}


// APP_BUILD
$build = "unknown";

if (file_exists(__DIR__ . "/../build.txt")) {
    $build = trim(file_get_contents(__DIR__ . "/../build.txt"));
} elseif (`which git`) {
    $gitSha1 = exec("git log --pretty=format:'%H' -n 1");
    $build = substr($gitSha1, 0, 7);
}

putenv("APP_BUILD=$build");

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
$app = new Laravel\Lumen\Application(realpath(__DIR__ . "/../"));
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
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);

$app->singleton(
    Illuminate\Contracts\Routing\ResponseFactory::class,
    Illuminate\Routing\ResponseFactory::class
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

$app->alias("mailer", \Illuminate\Contracts\Mail\Mailer::class);

/*
|--------------------------------------------------------------------------
| External configurations
|--------------------------------------------------------------------------
|
| Load configuration files from /config/
|
*/

$app->configure("auth");
$app->configure("backup");
$app->configure("cors");
$app->configure("filesystems");
$app->configure("logging");
$app->configure("mail");
$app->configure("queue");
$app->configure("services");


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
    Barryvdh\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    "login" => \Mathrix\Lumen\Middleware\LoggedMiddleware::class,
    "scope" => \Mathrix\Lumen\Middleware\ScopeMiddleware::class
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
$app->register(App\Providers\AppServiceProvider::class);

if (env("SENTRY_DSN") !== null) {
    $app->register(Sentry\SentryLaravel\SentryLumenServiceProvider::class);
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/
Dusterio\LumenPassport\LumenPassport::routes($app->router);

return $app;

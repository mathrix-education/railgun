<?php

use Carbon\Carbon;

require_once __DIR__ . "/../vendor/autoload.php";

try {
    (new Dotenv\Dotenv(__DIR__ . "/../"))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

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

/**
 * Fix the sitemap issue
 *
 * @see https://stackoverflow.com/questions/37945911/lumen-cache-store-is-not-instantiable
 */
$app->singleton(
    Illuminate\Cache\Repository::class,
    Illuminate\Contracts\Cache\Repository::class
);

/*
|--------------------------------------------------------------------------
| External configurations
|--------------------------------------------------------------------------
|
| Load configuration files from /config/
|
*/

$app->configure("auth");
$app->configure("cors");
$app->configure("mail");
$app->configure("services");
$app->configure("filesystems");
$app->configure("backup");
$app->configure("queue");

if ($app->environment("dev")) {
    $app->configure("ide-helper");
}


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
    "auth" => App\Middleware\Authenticate::class,
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

if ($app->environment("local", "dev")) {
    $app->register(Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
    $app->bind(
        Illuminate\Database\ConnectionResolverInterface::class,
        Illuminate\Database\ConnectionResolver::class
    );
}

$app->register(App\Providers\AppServiceProvider::class);


if ($app->environment("stage", "master")) {
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

$app->router->group(["namespace" => "App\\Controllers"], function ($router) {
    /** @var Laravel\Lumen\Routing\Router $router */
    $router->get("/", function () {
        $build = basename(dirname(__DIR__, 1));

        try {
            $build = Carbon::createFromFormat("Y-m-d_H-i-s", $build)->toIso8601String();
        } catch (InvalidArgumentException $e) {
        }

        return response()->json([
            "message" => "Welcome to Mathrix API version " . env("APP_VERSION") . " by Mathieu Bour",
            "commit" => trim(exec("git log --pretty=\"%h\" -n1 HEAD")),
            "build" => $build
        ]);
    });

    foreach (glob(__DIR__ . "/../app/Routes/*.php") as $routeFile) {
        /** @noinspection PhpIncludeInspection */
        require $routeFile;
    }
});


$app->router->group(["namespace" => "App\\Controllers\\Braintree", "prefix" => "braintree"], function ($router) {
    foreach (glob(__DIR__ . "/../app/Routes/Braintree/*.php") as $routeFile) {
        /** @noinspection PhpIncludeInspection */
        require $routeFile;
    }
});

return $app;

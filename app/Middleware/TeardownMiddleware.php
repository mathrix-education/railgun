<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Railgun\Logging\GoogleCloudJsonFormatter;
use Symfony\Component\HttpFoundation\Response;
use function app;

/**
 * Execute teardown process at the end of all request to preserve a clean the application state.
 */
class TeardownMiddleware
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * For now, we simply forward the request to the next middleware.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response The final response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Shutdown the application container gracefully.
     *
     * @param Request  $request  The Illuminate HTTP request.
     * @param Response $response The Illuminate HTTP response.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Logout the user from the application.
        $this->auth->guard()->logout();

        // Clear the in-memory cache
        Cache::store('request')->clear();

        // Log the request
        Log::info(GoogleCloudJsonFormatter::NO_MESSAGE);

        if ((int)($response->getStatusCode() / 100) !== 5) {
            return;
        }

        // HTTP 5XX: reset Lighthouse GraphQL
        app()->flush();
        Container::setInstance(require app()->basePath('bootstrap/app.php'));
    }
}

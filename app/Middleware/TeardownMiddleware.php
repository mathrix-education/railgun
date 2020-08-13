<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Execution\ErrorPool;
use Nuwave\Lighthouse\Execution\GraphQLRequest;
use Railgun\Logging\GoogleCloudJsonFormatter;
use Symfony\Component\HttpFoundation\Response;
use function app;

/**
 * Execute teardown process at the end of all request to preserve a clean the application state.
 */
class TeardownMiddleware
{
    protected Container $app;
    private Auth        $auth;

    public function __construct(Container $app, Auth $auth)
    {
        $this->app  = $app;
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
     *
     * @throws BindingResolutionException
     *
     * @codeCoverageIgnore
     */
    public function terminate(Request $request, Response $response): void
    {
        if (app()->runningUnitTests()) {
            // We may run assertions against the app
            return;
        }

        // Logout the user from the application.
        $this->auth->guard()->logout();

        // Lighthouse
        $this->app->make(ErrorPool::class)->clear();
        $this->app->forgetInstance(GraphQLRequest::class);

        // Clear the in-memory cache
        Cache::store('request')->clear();

        // Log the request
        Log::info(GoogleCloudJsonFormatter::NO_MESSAGE);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function app;

/**
 * Set the local based on the Accept-Language header.
 */
class LanguageMiddleware
{
    private const SUPPORTED_LOCALES = ['fr'];

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response The final response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->getPreferredLanguage(self::SUPPORTED_LOCALES));

        return $next($request);
    }
}

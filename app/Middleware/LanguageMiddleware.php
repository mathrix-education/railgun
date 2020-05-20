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

    private string $previousLocale;

    public function __construct()
    {
        $this->previousLocale = app()->getLocale();
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response The final response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->previousLocale = app()->getLocale();
        app()->setLocale($request->getPreferredLanguage(self::SUPPORTED_LOCALES));

        return $next($request);
    }

    /**
     * Restore the previous locale.
     */
    public function terminate(): void
    {
        app()->setLocale($this->previousLocale);
    }
}

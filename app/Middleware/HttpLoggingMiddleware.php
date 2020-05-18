<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Railgun\Logging\GoogleCloudJsonFormatter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log every request.
 */
class HttpLoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        Log::info(GoogleCloudJsonFormatter::NO_MESSAGE); // Http Request will be retrieved automatically

        return $response;
    }
}

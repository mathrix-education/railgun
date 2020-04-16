<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Http\Request;
use Throwable;
use function app;

/**
 * Thrown when a route is reach using a non allowed method.
 */
class MethodNotAllowedException extends HttpException
{
    /** @var int $statusCode The response status code. */
    protected int $statusCode = 405;

    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        if (!$message && app()->bound('request')) {
            /** @var Request $request */
            $request = app()->make('request');
            $data    = [
                'method' => $request->getMethod(),
                'route'  => $request->getPathInfo() ?: '/',
            ];

            $message = "Method {$data['method']} is not allowed on route {$data['route']}";
        }

        parent::__construct($message ?? 'Method not allowed', $previous);
    }
}

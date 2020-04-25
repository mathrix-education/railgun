<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Http\Request;
use Throwable;
use function app;

class NotFoundException extends HttpException
{
    /** @var int $statusCode The response status code. */
    protected int $statusCode = 404;

    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        if (!$message && app()->bound('request')) {
            /** @var Request $request */
            $request = app()->make('request');
            $data    = [
                'method' => $request->getMethod(),
                'route'  => $request->getPathInfo() ?: '/',
            ];
            $this->setData($data);

            $message = "Route {$data['method']} {$data['route']} does not exist";
        }

        parent::__construct($message ?? 'Not found', $previous);
    }
}

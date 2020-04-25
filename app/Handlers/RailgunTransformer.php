<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use Railgun\Exceptions\MethodNotAllowedException;
use Railgun\Exceptions\NotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as SymfonyMethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException as SymfonyMethodNotAllowedException;
use Throwable;
use function implode;

class RailgunTransformer implements TransformsExceptions
{
    /**
     * Transform a exception into another.
     *
     * @param Throwable $original The original exception.
     * @param Closure   $next     The next exception transformer.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original, Closure $next): Throwable
    {
        $exception = null;

        if ($original instanceof NotFoundHttpException) {
            $exception = new NotFoundException($original->getMessage(), $original);
        } elseif ($original instanceof SymfonyMethodNotAllowedHttpException) {
            $exception = new MethodNotAllowedException($original->getMessage(), $original);
            $exception->setHeaders($original->getHeaders());
        } elseif ($original instanceof SymfonyMethodNotAllowedException) {
            $exception = new MethodNotAllowedException($original->getMessage(), $original);
            $exception->setHeaders(['Allow' => implode(',', $original->getAllowedMethods())]);
        }

        return $next($exception ?? $original);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use function trans;

/**
 * Thrown an authentication is invalid.
 */
class InvalidAuthToken extends AccessDeniedHttpException implements RendersErrorsExtensions
{
    public function __construct(string $source = 'API', ?Throwable $previous = null)
    {
        parent::__construct(trans('auth.invalid_token', ['source' => $source]), $previous, 0, []);
    }

    /**
     * @inheritDoc
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCategory()
    {
        return 'auth';
    }

    /**
     * @inheritDoc
     */
    public function extensionsContent(): array
    {
        return [];
    }
}

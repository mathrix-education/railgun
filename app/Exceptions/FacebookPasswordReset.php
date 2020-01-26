<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

/**
 * Thrown when a user which account has been created through Facebook tries to reset his password.
 */
class FacebookPasswordReset extends BadRequestHttpException implements RendersErrorsExtensions
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Unable to reset a password', $previous, 0, []);
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

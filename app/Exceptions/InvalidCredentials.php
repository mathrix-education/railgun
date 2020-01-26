<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentials as JWTAuthInvalidCredentials;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

/**
 * Thrown when the user attempts to login with invalid credentials (email/password...).
 */
class InvalidCredentials extends JWTAuthInvalidCredentials implements RendersErrorsExtensions
{
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

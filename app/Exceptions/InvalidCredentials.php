<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use function trans;

/**
 * Thrown when the user attempts to login with invalid credentials (email/password...).
 */
class InvalidCredentials extends AccessDeniedHttpException implements RendersErrorsExtensions
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message ??= trans('auth.invalid_credentials');
        parent::__construct($message, $previous, 0, []);
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'auth';
    }

    public function extensionsContent(): array
    {
        return [];
    }
}

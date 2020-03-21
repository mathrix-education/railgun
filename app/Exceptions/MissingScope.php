<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use GraphQL\Error\ClientAware;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to access on a query, mutation or field and does not have the required scope.
 */
class MissingScope extends AccessDeniedHttpException implements ClientAware, RendersErrorsExtensions
{
    private array $scopes;

    public function __construct(array $scopes, ?Throwable $previous = null)
    {
        $this->scopes = $scopes;

        parent::__construct(
            trans('auth.missing_scope', ['scope' => implode(', ', $this->scopes)]),
            $previous
        );
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
        return [
            'scopes' => $this->scopes,
        ];
    }
}

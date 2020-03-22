<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Psr\Log\LogLevel;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to access on a query, mutation or field and does not have the required scope.
 */
class MissingScope extends RailgunException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'authentication';

    /** @var array The requested scopes. */
    private array $scopes;

    public function __construct(array $scopes, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('auth.missing_scope', ['scope' => implode(', ', $this->scopes)]),
            $previous
        );

        $this->scopes = $scopes;
    }

    public function extensionsContent(): array
    {
        return [
            'scopes' => $this->scopes,
        ];
    }
}

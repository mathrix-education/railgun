<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to access on a query, mutation or field and does not have the required scope.
 */
class MissingScopeException extends HttpException
{
    protected string $category = 'authorization';
    /** @var string[] The requested scopes. */
    private array $scopes;

    /**
     * @param string[]       $scopes   The scopes which were required but that the user does not have.
     * @param Throwable|null $previous The previous exception, if any.
     */
    public function __construct(array $scopes, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('auth.missing_scope', ['scope' => implode(', ', $scopes)]),
            $previous,
        );

        $this->scopes = $scopes;
    }

    /**
     * @return mixed[]
     */
    public function extensions(): array
    {
        return [
            'scopes' => $this->scopes,
        ];
    }
}

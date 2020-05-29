<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to access on a query, mutation or field and does not have the required permission.
 */
class MissingPermissionException extends HttpException
{
    protected string $category = 'authorization';
    /** @var string[] The requested permissions. */
    private array $permission;

    /**
     * @param string[]       $permissions The permissions which were required but that the user does not have.
     * @param Throwable|null $previous    The previous exception, if any.
     */
    public function __construct(array $permissions, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('auth.missing_permission', ['permission' => implode(', ', $permissions)]),
            $previous,
        );

        $this->permission = $permissions;
    }

    /**
     * @return mixed[]
     */
    public function extensions(): array
    {
        return [
            'permissions' => $this->permission,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Auth;

use Railgun\Models\User;
use function in_array;

class PermissionGate
{
    public const NAME = 'permission';

    /**
     * @param User|null $user       The user.
     * @param string    $permission The permission to check.
     *
     * @return bool If the user has the given permission.
     */
    public function __invoke(?User $user, string $permission): bool
    {
        if ($user === null) {
            return false;
        }

        if (in_array('*', $user->permissions, true)) {
            return true;
        }

        return in_array($permission, $user->permissions, true);
    }
}

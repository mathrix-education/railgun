<?php

declare(strict_types=1);

namespace Railgun\Support;

use Railgun\Models\User;
use function in_array;

class PermissionGate
{
    public const NAME = 'permission';

    public function __invoke(?User $user, string $permission): bool
    {
        if ($user === null) {
            return false;
        }

        if (in_array('*', $user->scopes, true)) {
            return true;
        }

        return in_array($permission, $user->scopes, true);
    }
}

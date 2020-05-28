<?php

declare(strict_types=1);

namespace Railgun\Policies;

use Railgun\Auth\PermissionGate;
use Railgun\Models\User;

class UserPolicy
{
    public function read(User $user, User $target): bool
    {
        return $user->is($target) || $user->can(PermissionGate::NAME, 'users:read');
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use function scope;

class UserPolicy extends BasePolicy
{
    public function get(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'users:read');
    }

    public function patch(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'users:write');
    }

    public function delete(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'users:delete');
    }

    public function points(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'users:write');
    }

    public function subscriptions(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'subscriptions:read');
    }

    public function products(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'products:read');
    }

    public function purchases(User $currentUser, User $user): bool
    {
        return $currentUser->is($user) || scope($currentUser, 'purchases:read');
    }
}

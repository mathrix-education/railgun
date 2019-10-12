<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Describe the user policy.
 */
class UserPolicy extends BasePolicy
{
    /**
     * Check that the current user is the same user as the target user or has the given scope.
     *
     * @param User        $current The current (logged) user.
     * @param User        $target  The target user.
     * @param string|null $scope   The required scope. May be null and if so, the scope check is ignored.
     *
     * @return bool If the action is permitted.
     */
    private function isOrHasScope(User $current, User $target, ?string $scope = null): bool
    {
        return $current->is($target) || ($scope !== null && $current->hasScope($scope));
    }

    /**
     * Check the user get permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the get action is permitted.
     */
    public function get(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'users:read');
    }

    /**
     * Check the user patch permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the patch action is permitted.
     */
    public function patch(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'users:write');
    }

    /**
     * Check the user delete permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the delete action is permitted.
     */
    public function delete(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'users:delete');
    }

    /**
     * Check the user points permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the points action is permitted.
     */
    public function points(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'users:write');
    }

    /**
     * Check the user points permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the points action is permitted.
     */
    public function subscriptions(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'subscriptions:read');
    }

    /**
     * Check the user products permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the products action is permitted.
     */
    public function products(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'products:read');
    }

    /**
     * Check the user products permission.
     *
     * @param User $current The current (logged) user.
     * @param User $target  The target user.
     *
     * @return bool If the products action is permitted.
     */
    public function purchases(User $current, User $target): bool
    {
        return $this->isOrHasScope($current, $target, 'purchases:read');
    }
}

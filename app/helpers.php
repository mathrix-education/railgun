<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Railgun\Models\User;

if (!function_exists('hasScope')) {
    /**
     * Check if a user has the given scope.
     *
     * @param User|null $user  The user to check.
     * @param string    $scope The scope to check.
     *
     * @return bool If the given user has the given scope.
     */
    function hasScope(?User $user, string $scope): bool
    {
        return $user instanceof User
            && (
                in_array('*', $user->scopes, true)
                || in_array($scope, $user->scopes, true)
            );
    }
}

if (!function_exists('scope')) {
    /**
     * Check if the current logged user has the given scope.
     *
     * @param string $scope The scope to check
     *
     * @return bool If the current user has the given scope.
     */
    function scope(string $scope): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return hasScope($user, $scope);
    }
}

if (!function_exists('isPremium')) {
    /**
     * @return bool Check if the current user is premium.
     */
    function isPremium(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user instanceof User && $user->premium;
    }
}

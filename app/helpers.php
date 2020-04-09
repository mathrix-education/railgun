<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Railgun\Models\User;

if (!function_exists('hasScope')) { // @codeCoverageIgnore

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

if (!function_exists('scope')) { // @codeCoverageIgnore

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

if (!function_exists('isPremium')) { // @codeCoverageIgnore

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

if (!function_exists('prefix')) { // @codeCoverageIgnore

    /**
     * Get the application prefix.
     *
     * @param string|null $path       An additional path to add.
     * @param bool        $underscore Of the prefix should be terminated by an underscore.
     *
     * @return string The generated prefix.
     */
    function prefix(?string $path = null, bool $underscore = false): string
    {
        $prefix  = env('APP_NAME', 'app') . '_' . env('APP_ENV', 'stable');
        $prefix .= $path ? "_$path" : '';
        $prefix .= $underscore && $prefix[strlen($prefix) - 1] !== '_' ? '_' : '';

        return $prefix;
    }
}

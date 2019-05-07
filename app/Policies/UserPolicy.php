<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class UserPolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class UserPolicy extends BasePolicy
{
    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function get(User $currentUser, User $user)
    {
        return $currentUser->is($user) || scope($currentUser, "users:read");
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function patch(User $currentUser, User $user)
    {
        return $currentUser->is($user) || scope($currentUser, "users:write");
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        return $currentUser->is($user) || scope($currentUser, "users:delete");
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function points(User $currentUser, User $user)
    {
        return $currentUser->is($user) || scope($currentUser, "users:write");
    }
}

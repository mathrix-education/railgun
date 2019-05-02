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
        $a = $currentUser->id === $user->id;
        $b = $currentUser->tokenCan("admin");

        return $a || $b;
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function patch(User $currentUser, User $user)
    {
        return  $currentUser->id === $user->id || $currentUser->tokenCan("admin");
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function delete(User $currentUser)
    {
        return $currentUser->tokenCan("admin");
    }


    /**
     * @param User $currentUser
     *
     * @return bool
     */
    public function search(User $currentUser)
    {
        return $currentUser->tokenCan("admin");
    }


    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function points(User $currentUser, User $user)
    {
        return $currentUser->tokenCan("admin") || $currentUser->id === $user->id;
    }
}

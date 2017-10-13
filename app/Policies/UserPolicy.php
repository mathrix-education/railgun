<?php

namespace App\Policies;

use App\Models\User;

/**
 * Class UserPolicy
 *
 * @author Mathrix Team
 * @package App\Policies
 */
class UserPolicy extends BasePolicy
{
    /**
     * @return bool
     */
    public function search()
    {
        return false;
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function get(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id ||
            (in_array($user->type, ['registered', 'premium']) && $currentUser->tokenCan('moderator'));
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function edit(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function points(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        // TODO: Really?
        return $currentUser->id === $user->id;
    }
}
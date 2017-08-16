<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function get(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $user->tokenCan('moderator');
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function edit(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $user->tokenCan('admin');
    }

    /**
     * @param User $currentUser
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $user->tokenCan('admin');
    }
}
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
    public function edit(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id || $user->tokenCan('moderator');
    }
}
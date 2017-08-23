<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    public function before(User $currentUser)
    {
        if ($currentUser->tokenCan('admin')) {
            return true;
        }
    }
}
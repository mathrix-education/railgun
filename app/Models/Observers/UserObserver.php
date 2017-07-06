<?php

namespace App\Models\Observers;


use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserObserver
 * @package App\Models\Observers
 */
class UserObserver
{
    /**
     * @param User $user
     */
    public function saving(User $user)
    {
        /*
         * Hash if needed:
         * - password needs to be set
         * - password length is not equal to 60
         * - password does not contain the CRYPT_BLOWFISH identifier $2y$
         * Not 100% sure but practically null
         */
        if (isset($user->password) && strlen($user->password) != 60 && !strpos($user->password, '$2y$')) {
            $user->password = Hash::make($user->password);
        }
    }
}
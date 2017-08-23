<?php

namespace App\Models\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\Observers\BaseObserver;

/**
 * Class UserObserver
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class UserObserver extends BaseObserver
{
    /**
     * @param User $user
     *
     * @return bool|null|void
     */
    public function saving($user)
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

        parent::saving($user);
    }
}
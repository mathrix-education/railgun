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
    public function saving(User $user) {
        if(isset($user->password)) {
            $user->password = Hash::make($user->password);
        }
    }
}
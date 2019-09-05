<?php

namespace App\Observers;

use App\Models\Level;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class UserObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class UserObserver extends BaseObserver
{
    /**
     * @param User $user
     */
    public function creating($user)
    {
        $user->token = Factory::create()->uuid;
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function saving($user)
    {
        // Password hash check
        if ($user->isDirty("password") && Hash::needsRehash($user->password)) {
            $user->password = Hash::make($user->password);
        }

        return true;
    }
}

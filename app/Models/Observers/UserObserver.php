<?php

namespace App\Models\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Level;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class UserObserver extends BaseObserver
{
    /**
     * @param User $user
     *
     * @return bool
     */
    public function saving($user)
    {
        if ($user->isDirty('password')) {
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

        if ($user->isDirty('points')) {
            /** @var int $pointsAdded the points added during the current request */
            $pointsAdded = $user->points - $user->getOriginal('points');

            /*
            |--------------------------------------------------------------------------
            | Trigger the daily_achievements incrementation
            |--------------------------------------------------------------------------
            */
            if ($user->daily_points + $pointsAdded >= $user->daily) {
                $user->daily_achievements++;
            }


            /*
            |--------------------------------------------------------------------------
            | Trigger the level achievement
            |--------------------------------------------------------------------------
            */
            /** @var Level $nextLevel */
            $nextLevel = Level::where('level', $user->level + 1)->first();

            while ($nextLevel !== null && $user->points >= $nextLevel->objective) {
                $user->level++;
                $nextLevel = Level::where('level', $user->level + 1)->first();
            }
        }

        return true;
    }


    /**
     * @param User $user
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function created($user)
    {
        Log::log('user_registered', $user);
    }


    /**
     * @param User $user
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function updated($user)
    {
        $changes = array_keys($user->getDirty());

        if (in_array('password', $changes)) {
            Log::log('user_changed_password', $user);
        }
        if (in_array('daily', $changes)) {
            Log::log('user_set_daily', $user);
        }
        if (in_array('level', $changes)) {
            Log::log('user_passed_level', $user, [
                'from' => $user->level - 1,
                'to' => $user->level
            ]);
        }
    }
}


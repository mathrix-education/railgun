<?php

namespace App\Models\Observers;

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
     */
    public function created($user)
    {
        Log::log('user_registered', $user);
    }


    /**
     * @param User $user
     */
    public function updating($user)
    {
        $changes = array_keys($user->getDirty());

        if (in_array('points', $changes)) {
            $pointsBefore = $user->getOriginal('points');
            $pointsAfter = $user->points;
            $pointsAdded = $pointsAfter - $pointsBefore;
            $sum = $pointsAdded;

            $logs = Log::where([
                ['user_id', '=', $user->id],
                ['event', '=', 'user_gathered_points']

            ])
                ->whereBetween('created_at', [Carbon::now()->setTime(0, 0, 0), Carbon::now()]);

            /** @var Log $log */
            foreach ($logs as $log) {
                $sum += $log->value['points'];
            }

            if ($sum >= $user->daily) {
                $user->daily_achievements++;
            }
        }
    }


    /**
     * @param User $user
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
    }


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
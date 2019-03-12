<?php

namespace App\Observers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Level;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
 */
class UserObserver extends BaseObserver
{
    /**
     * @param User $user
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     *
     * @return bool
     */
    public function saving($user)
    {
        // Password hash check
        if ($user->isDirty("password")) {
            /*
             * Hash if needed:
             * - password needs to be set
             * - password length is not equal to 60
             * - password does not contain the CRYPT_BLOWFISH identifier $2y$
             * Not 100% sure but practically null
             */
            if (isset($user->password) && \mb_strlen($user->password) !== 60 && !mb_strpos($user->password, "$2y$")) {
                $user->password = Hash::make($user->password);
            }
        }

        if ($user->isDirty("points")) {
            /** @var int $pointsAdded the points added during the current request */
            $pointsAdded = $user->points - $user->getOriginal("points");

            /*
            |--------------------------------------------------------------------------
            | Trigger the daily_achievements incrementation
            |--------------------------------------------------------------------------
            */
            $alreadyAchievedDaily = Log::where([
                "user_id" => $user->id,
                "event" => "user_achieved_daily",
            ])
                ->whereBetween("created_at", [
                    Carbon::now()
                        ->setTime(0, 0, 0),
                    Carbon::now(),
                ])
                ->exists();

            if (!$alreadyAchievedDaily && $user->daily_points + $pointsAdded >= $user->daily) {
                ++$user->daily_achievements;
                Log::log("user_achieved_daily", $user);
            }

            /*
            |--------------------------------------------------------------------------
            | Trigger the level achievement
            |--------------------------------------------------------------------------
            */
            /** @var Level $nextLevel */
            $nextLevel = Level::where("level", $user->level + 1)
                ->first();

            while ($nextLevel !== null && $user->points >= $nextLevel->objective) {
                ++$user->level;
                $nextLevel = Level::where("level", $user->level + 1)
                    ->first();
            }
        }

        return true;
    }


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
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function created($user)
    {
        Log::log("user_registered", $user);
    }


    /**
     * @param User $user
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     */
    public function updated($user)
    {
        $changes = array_keys($user->getDirty());

        if (\in_array("password", $changes, true)) {
            Log::log("user_changed_password", $user);
        }
        if (\in_array("daily", $changes, true)) {
            Log::log("user_set_daily", $user);
        }
        if (\in_array("level", $changes, true)) {
            Log::log("user_passed_level", $user, [
                "from" => $user->level - 1,
                "to" => $user->level,
            ]);
        }
    }
}

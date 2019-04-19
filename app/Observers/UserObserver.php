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
     *
     * @return bool
     * @throws Http400BadRequestException
     */
    public function saving($user)
    {
        // Password hash check
        if ($user->isDirty("password") && Hash::needsRehash($user->password)) {
            $user->password = Hash::make($user->password);
        }

        if ($user->isDirty("points")) {
            /** @var int $pointsAdded the points added during the current request */
            $pointsAdded = $user->points - $user->getOriginal("points");

            /*
            |--------------------------------------------------------------------------
            | Trigger the daily_achievements incrementation
            |--------------------------------------------------------------------------
            */
            $alreadyAchievedDaily = Log::query()
                ->where([
                    "user_id" => $user->id,
                    "event" => "user_achieved_daily",
                ])
                ->whereBetween("created_at", [
                    Carbon::now()->setTime(0, 0, 0),
                    Carbon::now(),
                ])
                ->exists();

            if (!$alreadyAchievedDaily && $user->daily_points + $pointsAdded >= $user->daily) {
                $user->daily_achievements++;
                Log::log("user_achieved_daily", $user);
            }

            /*
            |--------------------------------------------------------------------------
            | Trigger the level achievement
            |--------------------------------------------------------------------------
            */
            /** @var Level $nextLevel */
            $nextLevel = Level::query()
                ->where("level", $user->level + 1)
                ->first();

            while ($nextLevel !== null && $user->points >= $nextLevel->objective) {
                ++$user->level;
                $nextLevel = Level::query()
                    ->where("level", $user->level + 1)
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
     * @throws Exception
     */
    public function created($user)
    {
        Log::log("user_registered", $user);
    }


    /**
     * @param User $user
     *
     * @throws Http400BadRequestException
     */
    public function updated($user)
    {
        $changes = array_keys($user->getDirty());

        if (in_array("password", $changes, true)) {
            Log::log("user_changed_password", $user);
        }
        if (in_array("daily", $changes, true)) {
            Log::log("user_set_daily", $user);
        }
        if (in_array("level", $changes, true)) {
            Log::log("user_passed_level", $user, [
                "from" => $user->level - 1,
                "to" => $user->level,
            ]);
        }
    }
}

<?php
namespace Tests\Users;

use App\Models\Level;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class UsersAsRegisteredTest
 *
 * @author Mathrix Back-End Team
 * @package Tests\Users
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class UsersAsRegisteredTest extends UsersAsPublicTest
{
    /**
     * GET /users/me
     */
    public function testMe()
    {
        $this->call("get", "/users/me");

        $this->assertHttp200Success();
    }


    /**
     * PATCH /users/{id}
     */
    public function testEdit()
    {
        $newUser = factory(User::class, $this->user->type)->raw();
        unset($newUser["password"], $newUser["token"], $newUser["settings"], $newUser["points"], $newUser["level"]);

        $this->call("patch", "/users/" . $this->user->id, $newUser);

        $this->assertHttp200Success();
        $this->assertInDatabase("users", $newUser);
    }


    /**
     * POST /users/points without loggable
     */
    public function testPointsWithoutLoggable()
    {
        $pointsBefore = $this->user->points;
        $pointsToAdd = $this->faker->numberBetween(1, 10);

        $this->call("post", "/users/points", ["points" => $pointsToAdd]);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", [
            "id" => $this->user->id,
            "points" => $pointsBefore + $pointsToAdd
        ]);
        $this->assertLogged("user_gathered_points", $this->user);
    }


    /**
     * POST /users/points with loggable
     */
    public function testPointsWithLoggable()
    {
        $pointsBefore = $this->user->points;
        $pointsToAdd = $this->faker->numberBetween(1, 10);

        $userModulesIdsToday = Log::where([
            ["event", "user_gathered_points"],
            ["user_id", $this->user->id],
            ["loggable_type", "module"],
            ["created_at", ">=", Carbon::now()->setTime(0, 0, 0)]
        ])->pluck("loggable_id");

        /** @var Module $loggable */
        $loggable = Module::inRandomOrder()
            ->whereNotIn("id", $userModulesIdsToday)
            ->firstOrFail();

        $this->call("post", "/users/points", [
            "points" => $pointsToAdd,
            "loggable_type" => "module",
            "loggable_id" => $loggable->id
        ]);


        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", [
            "id" => $this->user->id,
            "points" => $pointsBefore + $pointsToAdd
        ]);
        $this->assertLogged("user_gathered_points", $this->user);

    }


    /**
     * POST /users/points with daily achievement trigger
     *
     * @throws \Exception
     */
    public function testPointsWithDailyAchievementPass()
    {
        $dailyAchievementsBefore = $this->user->daily_achievements;
        $potentialLog = Log::where([
            "event" => "user_achieved_daily",
            "user_id" => $this->user->id
        ])
            ->whereBetween("created_at", [
                Carbon::now()->setTime(0, 0, 0),
                Carbon::now()
            ]);

        // Delete daily achievement log if exists
        if ($potentialLog->exists()) {
            $potentialLog->delete();
        }

        $this->call("post", "/users/points", ["points" => 100]); // To be sure to pass a daily achievement

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", [
            "id" => $this->user->id,
            "daily_achievements" => $dailyAchievementsBefore + 1
        ]);
    }


    /**
     * POST /users/points with level pass trigger
     */
    public function testPointsWithLevelPass()
    {
        /** @var Level $level */
        $level = Level::where("level", $this->user->level + 1)->first();
        $levelBefore = $this->user->level;
        $pointsToAdd = $level->objective - $this->user->points + 1; // +1 to be sure to pass the level

        $this->call("post", "/users/points", ["points" => $pointsToAdd]);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", [
            "id" => $this->user->id,
            "level" => $levelBefore + 1
        ]);
    }
}


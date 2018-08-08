<?php
namespace Tests\API\Users;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Class_;
use App\Models\Level;
use App\Models\Log;
use App\Models\Module;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

/**
 * Class UsersAsRegisteredTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Users
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class UsersAsRegisteredTest extends UsersAsPublicTest
{
    /**
     * POST /users/register
     */
    public function testRegisterAdmin()
    {
        $dataWithoutPassword = [
            "email" => $this->faker->email,
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id,
            "type" => "admin"
        ];
        $data = array_merge($dataWithoutPassword, ["password" => "test"]);

        $this->call("post", "/users/register", $data);

        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase("users", $dataWithoutPassword);
    }


    /**
     * GET /users/{id}
     */
    public function testGet()
    {
        $this->call("get", "/users/{$this->user->id}");

        $this->assertHttp200Success();
    }


    /**
     * GET /users/{id}
     */
    public function testGetStranger()
    {
        $stranger = $this->stranger();

        $this->call("get", "/users/{$stranger->id}");

        $this->assertHttp401Unauthorized();
    }


    /**
     * GET /users/profile
     */
    public function testProfile()
    {
        $this->call("get", "/users/profile");

        $this->assertHttp200Success();
    }


    /**
     * PATCH /users/{id}
     */
    public function testEdit()
    {
        $newUser = factory(User::class, $this->user->type)->raw();
        unset($newUser["password"], $newUser["token"], $newUser["settings"], $newUser["points"], $newUser["level"],
            $newUser["stripe_id"], $newUser["created_at"]);

        $this->call("patch", "/users/" . $this->user->id, $newUser);

        $this->assertHttp200Success();
        $this->assertInDatabase("users", $newUser);
    }


    /**
     * PATCH /users/{id} with the current_password field
     *
     * @throws \Exception
     */
    public function testEditPassword()
    {
        $newPassword = "test2";
        $newUser = factory(User::class, $this->user->type)->raw();
        unset($newUser["token"], $newUser["settings"], $newUser["points"], $newUser["level"], $newUser["stripe_id"],
            $newUser["created_at"]);
        $newUser["password"] = $newPassword;
        $newUser["current_password"] = "test";

        $this->call("patch", "/users/" . $this->user->id, $newUser);

        $responseData = $this->getResponseData();
        unset($newUser["current_password"], $newUser["password"]);

        $this->assertHttp200Success();
        $this->assertInDatabase("users", $newUser);

        $hashedPassword = User::whereId($responseData->id)->first()->password;

        $this->assertTrue(Hash::check($newPassword, $hashedPassword), "New hashed password does not match " .
            "with the one in the database");
    }


    /**
     * PATCH /users/{id} with the incorrect current_password field
     *
     * @throws \Exception
     */
    public function testEditPasswordWithIncorrectCurrentPassword()
    {
        $newPassword = "test2";
        $newUser = factory(User::class, $this->user->type)->raw();
        unset($newUser["token"], $newUser["settings"], $newUser["points"], $newUser["level"],
            $newUser["created_at"]);
        $newUser["password"] = $newPassword;
        $newUser["current_password"] = "test_incorrect";

        $this->call("patch", "/users/" . $this->user->id, $newUser);

        $this->assertHttp400InvalidRequest();

        unset($newUser["current_password"], $newUser["password"]);

        $this->assertNotInDatabase("users", $newUser);
    }


    /**
     * PATCH /users/{id} without the current_password field
     *
     * @throws \Exception
     */
    public function testEditPasswordWithoutCurrentPasswordField()
    {
        $newPassword = "test2";
        $newUser = factory(User::class, $this->user->type)->raw();
        unset($newUser["token"], $newUser["settings"], $newUser["points"], $newUser["level"],
            $newUser["created_at"]);
        $newUser["password"] = $newPassword;

        $this->call("patch", "/users/" . $this->user->id, $newUser);

        $this->assertHttp400InvalidRequest();
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
     * POST /users/points wrong loggable
     */
    public function testPointsWithWrongLoggable()
    {
        $this->call("post", "/users/points", [
            "points" => $this->faker->numberBetween(1, 10),
            "loggable_type" => "wrongloggable",
            "loggable_id" => 6664242
        ]);

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/points without points field
     */
    public function testPointsWithoutPointsField()
    {
        $this->call("post", "/users/points");

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/points without points field
     */
    public function testPointsWithInvalidPointsField()
    {
        $this->call("post", "/users/points", ["points" => "lol"]);

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/points with loggable
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function testPointsWithLoggableAlreadyGatheredPoints()
    {
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

        Log::log("user_gathered_points", $this->user, [
            "loggable_type" => "module",
            "loggable_id" => $loggable->id
        ]);

        $this->call("post", "/users/points", [
            "points" => $pointsToAdd,
            "loggable_type" => "module",
            "loggable_id" => $loggable->id
        ]);


        // Assertions
        $this->assertHttp429TooManyRequests();
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


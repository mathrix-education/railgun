<?php
namespace Tests\Users;

use App\Models\Level;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Tests\ScopeTestCase;

class UsersAsRegisteredTest extends ScopeTestCase
{
    public function testEdit()
    {
        $newUser = factory(User::class, $this->user->type)->raw();

        // TODO: Handle cancerous fields
        unset($newUser['password']);
        unset($newUser['token']);
        unset($newUser['settings']);

        $this->call('patch', '/users/' . $this->user->id, $newUser);

        $this->assertHttp200Success();
        $this->assertInDatabase('users', $newUser);
    }


    public function testPointsWithoutLoggable()
    {
        $pointsBefore = $this->user->points;
        $pointsToAdd = $this->faker->numberBetween(1, 10);

        $this->call('post', '/users/points', ['points' => $pointsToAdd]);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase('users', [
            'id' => $this->user->id,
            'points' => $pointsBefore + $pointsToAdd
        ]);
        $this->assertLogged('user_gathered_points', $this->user);
    }


    public function testPointsWithLoggable()
    {
        $pointsBefore = $this->user->points;
        $pointsToAdd = $this->faker->numberBetween(1, 10);

        $userModulesIdsToday = Log::where([
            ['event', 'user_gathered_points'],
            ['user_id', $this->user->id],
            ['loggable_type', 'module'],
            ['created_at', '>=', Carbon::now()->setTime(0, 0, 0)]
        ])->pluck('loggable_id');

        /** @var Module $loggable */
        $loggable = Module::inRandomOrder()
            ->whereNotIn('id', $userModulesIdsToday)
            ->firstOrFail();

        $this->call('post', '/users/points', [
            'points' => $pointsToAdd,
            'loggable_type' => 'module',
            'loggable_id' => $loggable->id
        ]);


        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase('users', [
            'id' => $this->user->id,
            'points' => $pointsBefore + $pointsToAdd
        ]);
        $this->assertLogged('user_gathered_points', $this->user);

    }


    public function testDailyAchievementPass()
    {
        $dailyAchievementsBefore = $this->user->daily_achievements;

        $this->call('post', '/users/points', ['points' => 100]);// To be sure to pass a daily achievement

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase('users', [
            'id' => $this->user->id,
            'daily_achievements' => $dailyAchievementsBefore + 1
        ]);
    }


    public function testLevelPass()
    {
        /** @var Level $level */
        $level = Level::where('level', $this->user->level + 1)->first();
        $levelBefore = $this->user->level;
        $pointsToAdd = $level->objective - $this->user->points + 1; // +1 to be sure to pass the level

        $this->call('post', '/users/points', ['points' => $pointsToAdd]);

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase('users', [
            'id' => $this->user->id,
            'level' => $levelBefore + 1
        ]);
    }


}


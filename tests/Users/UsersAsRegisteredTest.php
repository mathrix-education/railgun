<?php
namespace Tests\Users;

use App\Models\Activity;
use App\Models\Log;
use App\Models\Module;
use Carbon\Carbon;
use Tests\ScopeTestCase;

class UsersAsRegisteredTest extends ScopeTestCase
{
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
}
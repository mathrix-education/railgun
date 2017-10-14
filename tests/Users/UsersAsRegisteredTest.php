<?php
namespace Tests\Users;

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
    }
}
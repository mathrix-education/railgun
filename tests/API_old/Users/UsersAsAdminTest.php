<?php

namespace Tests\API_old\Users;

use App\Models\Grade;
use App\Models\User;

/**
 * Class UsersAsAdminTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Users
 */
class UsersAsAdminTest extends UsersAsModeratorTest
{
    /**
     * POST /users/register.
     */
    public function testRegisterAdmin()
    {
        $dataWithoutPassword = [
            "email" => $this->faker->email,
            "class_id" => Grade::inRandomOrder()->firstOrFail()->id,
            "type" => "admin",
        ];
        $data = array_merge($dataWithoutPassword, ["password" => "test"]);

        $this->json("post", "/users/register", $data);

        $user = User::whereEmail($data["email"])->firstOrFail();

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", $dataWithoutPassword);
        $this->assertInDatabase("logs", [
            "event" => "user_registered",
            "user_id" => $user->id,
        ]);
    }


    /**
     * GET /users/{id}.
     */
    public function testGetStranger()
    {
        $stranger = $this->stranger();

        $this->json("get", "/users/{$stranger->id}");

        $this->assertHttp200Success();
    }


    public function testChangeType()
    {
        $user = User::whereType("registered")
            ->inRandomOrder()
            ->firstOrFail();

        $this->json("patch", "/users/{$user->id}", [
            "type" => "moderator",
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("users", [
            "email" => $user->email,
            "type" => "moderator",
        ]);
    }
}

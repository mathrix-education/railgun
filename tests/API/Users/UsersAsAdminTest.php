<?php

namespace Tests\API\Users;

use App\Models\Class_;
use App\Models\User;

/**
 * Class UsersAsAdminTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
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
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id,
            "type" => "admin",
        ];
        $data = array_merge($dataWithoutPassword, ["password" => "test"]);

        $this->call("post", "/users/register", $data);

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

        $this->call("get", "/users/{$stranger->id}");

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

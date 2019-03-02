<?php
namespace Tests\API\Users;

use App\Models\Class_;
use App\Models\User;
use Tests\API\ScopeTestCase;

/**
 * Class UsersAsPublicTest.
 * Test users as public user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Users
 */
class UsersAsPublicTest extends ScopeTestCase
{
    /**
     * POST /users/register.
     */
    public function testRegister()
    {
        if ($this->user !== null) {
            return;
        }

        $dataWithoutPassword = [
            "username" => $this->faker->userName,
            "email" => $this->faker->email,
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id,
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
     * GET /users/profile.
     */
    public function testProfile()
    {
        $this->json("get", "/users/profile");

        $this->assertHttp401Unauthorized();
    }

    /**
     * GET /users/exists.
     */
    public function testExistsTrue()
    {
        $user = User::inRandomOrder()->first();
        $this->json("post", "/users/exists", ["email" => $user->email]);

        $response = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertTrue($response->exists);
    }

    /**
     * GET /users/exists.
     */
    public function testExistsFalse()
    {
        $this->json("post", "/users/exists", ["email" => "notexisting@email.com"]);

        $response = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertFalse($response->exists);
    }

    /**
     * POST /users/search.
     */
    public function testSearch()
    {
        $this->json("post", "/users/search");

        $this->assertHttp401Unauthorized();
    }
}

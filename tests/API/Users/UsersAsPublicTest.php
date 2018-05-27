<?php

namespace Tests\API\Users;

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Models\Class_;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;
use Tests\API\ScopeTestCase;

/**
 * Class UsersAsPublicTest
 * Test users as public user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Quotes
 */
class UsersAsPublicTest extends ScopeTestCase
{
    /**
     * POST /users/register
     */
    public function testRegister()
    {
        if ($this->user !== null) {
            return;
        }

        $dataWithoutPassword = [
            "email" => $this->faker->email,
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id,
        ];
        $data = array_merge($dataWithoutPassword, ["password" => "test"]);

        $this->call("post", "/users/register", $data);

        $user = User::whereEmail($data["email"])->firstOrFail();

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase("users", $dataWithoutPassword);
        $this->assertInDatabase("logs", [
            "event" => "user_registered",
            "user_id" => $user->id
        ]);
    }


    /**
     * GET /users/profile
     */
    public function testProfile()
    {
        $this->call("get", "/users/profile");

        $this->assertHttp401Unauthorized();
    }


    /**
     * GET /users/exists
     */
    public function testExistsTrue()
    {
        $user = User::inRandomOrder()->first();
        $this->call("post", "/users/exists", ["email" => $user->email]);

        $response = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertTrue($response->exists);
    }


    /**
     * GET /users/exists
     */
    public function testExistsFalse()
    {
        $this->call("post", "/users/exists", ["email" => "notexisting@email.com"]);

        $response = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertFalse($response->exists);
    }


    /**
     * POST /users/search
     */
    public function testSearch()
    {
        $this->call("post", "/users/search");

        $this->assertHttp401Unauthorized();
    }
}

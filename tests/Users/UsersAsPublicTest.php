<?php

namespace Tests\Users;

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Models\Class_;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\ScopeTestCase;

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
        $dataWithoutPassword = [
            "email" => $this->faker->email,
            "class_id" => Class_::inRandomOrder()->firstOrFail()->id
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
     * POST /users/login
     *
     * @throws \Exception
     */
    public function testLogin()
    {
        /** @var User $user */
        $user = User::inRandomOrder()->firstOrFail();

        $this->call("post", "/users/login", [
            "grant_type" => "password",
            "client_id" => env("OAUTH_CLIENT_ID"),
            "client_secret" => env("OAUTH_CLIENT_SECRET"),
            "username" => $user->email,
            "password" => "test"
        ]);

        // Assertions
        $data = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertEquals("Bearer", $data->token_type);
        $this->assertGreaterThan(0, $data->expires_in);
        $this->assertNotEmpty($data->access_token);
        $this->assertNotEmpty($data->refresh_token);

        return $data;
    }


    /**
     * POST /users/login
     *
     * @depends testLogin
     *
     * @param \stdClass $data
     *
     * @throws \Exception
     */
    public function testRefresh(\stdClass $data)
    {
        /** @var User $user */
        $user = User::inRandomOrder()->firstOrFail();

        $this->call("post", "/users/login", [
            "grant_type" => "password",
            "client_id" => env("OAUTH_CLIENT_ID"),
            "client_secret" => env("OAUTH_CLIENT_SECRET"),
            "username" => $user->email,
            "password" => "test"
        ]);
        $data = $this->getResponseData();

        //$this->refreshApplication();

        $this->post("/users/refresh", [
            "grant_type" => "refresh_token",
            "client_id" => env("OAUTH_CLIENT_ID"),
            "client_secret" => env("OAUTH_CLIENT_SECRET"),
            "username" => $user->email,
            "refresh_token" => $data->refresh_token
        ]);

        $data = $this->getResponseData();
        $this->assertHttp200Success();
        $this->assertEquals("Bearer", $data->token_type);
        $this->assertGreaterThan(0, $data->expires_in);
        $this->assertNotEmpty($data->access_token);
    }


    /**
     * POST /users/forgot
     */
    public function testForgot()
    {
        Mail::fake();
        $user = User::inRandomOrder()->where("settings", "NOT LIKE", "%facebook_id%")
            ->first();

        $this->call("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp200Success();
        Mail::assertQueued(PasswordForgottenMail::class, function (PasswordForgottenMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }


    /**
     * POST /users/forgot
     */
    public function testForgotAsFacebookUser()
    {
        $user = User::inRandomOrder()->where("settings", "LIKE", "%facebook_id%")->first();
        $this->call("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/reset
     */
    public function testReset()
    {
        Mail::fake();

        $user = User::inRandomOrder()->first();
        $this->call("post", "/users/reset/{$user->token}", ["password" => "test"]);

        $this->assertHttp200Success();
        Mail::assertQueued(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }


    /**
     * POST /users/reset
     */
    public function testResetWithEmptyPassword()
    {
        $user = User::inRandomOrder()->first();
        $this->call("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/reset
     */
    public function testResetAsFacebookUser()
    {
        $user = User::inRandomOrder()->where("settings", "LIKE", "%facebook_id%")->first();
        $this->call("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
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

        $this->assertHttp200Success();
    }
}

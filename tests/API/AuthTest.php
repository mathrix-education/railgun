<?php
namespace Tests\API;

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Models\User;
use App\Notifications\PasswordForgottenNotification;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laravel\Lumen\Testing\TestCase;
use Tests\Traits\Debug;
use Tests\Traits\HttpAssertionsTrait;
use Tests\Traits\JsonAssertionsTrait;
use Tests\Traits\ResponseTrait;

/**
 * Class AuthTest
 *
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\API
 */
class AuthTest extends TestCase
{
    use ResponseTrait, JsonAssertionsTrait, HttpAssertionsTrait, Debug;


    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . "/../../bootstrap/app.php";
    }


    /**
     * Get a Facebook user
     *
     * @return User
     */
    private function getFacebookUser(): User
    {
        return User::inRandomOrder()
            ->where("settings", "LIKE", "%facebook_id%")
            ->firstOrFail();
    }


    /**
     * Get regular (non Facebook) user
     *
     * @return User
     */
    private function getNonFacebookUser(): User
    {
        return User::inRandomOrder()
            ->where("settings", "NOT LIKE", "%facebook_id%")
            ->firstOrFail();
    }


    /**
     * POST /users/login
     *
     * @throws \Exception
     */
    public function testLogin()
    {
        $user = $this->getNonFacebookUser();

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

        $args = new \stdClass();
        $args->email = $user->email;
        $args->refresh_token = $data->refresh_token;

        return $args;
    }


    /**
     * POST /users/refresh
     *
     * @depends testLogin
     *
     * @param \stdClass $args
     *
     * @throws \Exception
     */
    public function testRefresh($args)
    {
        $this->post("/users/refresh", [
            "grant_type" => "refresh_token",
            "client_id" => env("OAUTH_CLIENT_ID"),
            "client_secret" => env("OAUTH_CLIENT_SECRET"),
            "username" => $args->email,
            "refresh_token" => $args->refresh_token
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
        $user = $this->getNonFacebookUser();
        Notification::fake();

        $this->call("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp200Success();
        Notification::assertSentTo($user, PasswordForgottenNotification::class,
            function (PasswordForgottenNotification $notification, array $channels) use ($user) {
                return $channels[0] === "mail";
            }
        );
    }


    /**
     * POST /users/forgot
     */
    public function testForgotAsFacebookUser()
    {
        $user = $this->getFacebookUser();
        $this->call("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/reset
     */
    public function testReset()
    {
        $user = $this->getNonFacebookUser();
        Notification::fake();

        $this->call("post", "/users/reset/{$user->token}", ["password" => "test"]);

        $this->assertHttp200Success();
        Notification::assertSentTo($user, PasswordResetNotification::class,
            function (PasswordResetNotification $notification, array $channels) use ($user) {
                return $channels[0] === "mail";
            }
        );
    }


    /**
     * POST /users/reset
     */
    public function testResetWithEmptyPassword()
    {
        $user = $this->getNonFacebookUser();

        $this->call("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /users/reset
     */
    public function testResetAsFacebookUser()
    {
        $user = $this->getFacebookUser();

        $this->call("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
    }
}

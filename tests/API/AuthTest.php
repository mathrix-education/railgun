<?php
namespace Tests\API;

use App\Models\User;
use App\Notifications\PasswordForgottenNotification;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Lumen\Testing\TestCase;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\HttpAssertionsTrait;
use Tests\Utils\Traits\Assertions\JsonAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;
use Tests\Utils\Traits\DebugTrait;
use Tests\Utils\Traits\ResponseTrait;

/**
 * Class AuthTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\API
 */
class AuthTest extends TestCase
{
    use DatabaseAssertionsTrait, HttpAssertionsTrait, JsonAssertionsTrait, NotificationsAssertionsTrait, ResponseTrait, DebugTrait;

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
     * POST /users/login.
     *
     * @throws \Exception
     */
    public function testLogin()
    {
        $user = $this->getNonFacebookUser();

        $this->json("post", "/users/login", [
            "grant_type" => "password",
            "client_id" => env("OAUTH_CLIENT_ID"),
            "client_secret" => env("OAUTH_CLIENT_SECRET"),
            "username" => $user->email,
            "password" => "test123",
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
     * POST /users/refresh.
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
            "refresh_token" => $args->refresh_token,
        ]);

        $data = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertEquals("Bearer", $data->token_type);
        $this->assertGreaterThan(0, $data->expires_in);
        $this->assertNotEmpty($data->access_token);
    }

    /**
     * POST /users/forgot.
     */
    public function testForgot()
    {
        $user = $this->getNonFacebookUser();
        Notification::fake();

        $this->json("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp200Success();
        $this->assertNotificationSentToVia(PasswordForgottenNotification::class, $user, "mail");
    }

    /**
     * POST /users/forgot.
     */
    public function testForgotAsFacebookUser()
    {
        $user = $this->getFacebookUser();
        $this->json("post", "/users/forgot", ["email" => $user->email]);

        $this->assertHttp400InvalidRequest();
    }

    /**
     * POST /users/reset.
     */
    public function testReset()
    {
        $user = $this->getNonFacebookUser();
        Notification::fake();

        $this->json("post", "/users/reset/{$user->token}", ["password" => "test"]);

        $this->assertHttp200Success();
        $this->assertNotificationSentToVia(PasswordResetNotification::class, $user, "mail");

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update(["password" => Hash::make("test123")]);
    }

    /**
     * POST /users/reset.
     */
    public function testResetWithEmptyPassword()
    {
        $user = $this->getNonFacebookUser();

        $this->json("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
    }

    /**
     * POST /users/reset.
     */
    public function testResetAsFacebookUser()
    {
        $user = $this->getFacebookUser();

        $this->json("post", "/users/reset/{$user->token}", ["password" => null]);

        $this->assertHttp400InvalidRequest();
    }

    /**
     * Get regular (non Facebook) user.
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
     * Get a Facebook user.
     *
     * @return User
     */
    private function getFacebookUser(): User
    {
        return User::inRandomOrder()
            ->where("settings", "LIKE", "%facebook_id%")
            ->firstOrFail();
    }
}

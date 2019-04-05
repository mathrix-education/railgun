<?php

namespace Tests\API\Auth;

use App\Models\User;
use App\Notifications\PasswordForgottenNotification;
use Illuminate\Support\Facades\Notification;
use Tests\API\APITestCase;

/**
 * Class AuthRefreshTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class AuthForgotTest extends APITestCase
{
    /**
     * POST /users/forgot
     */
    public function testForgot()
    {
        Notification::fake();

        $user = User::random(["settings", "NOT LIKE", "%facebook_id%"]);
        $this->json("post", "/auth/forgot", ["email" => $user->email]);

        $this->assertResponseOk();
        $this->assertNotInDatabase("users", ["id" => $user->id, "token" => null]);
        $this->assertOpenAPIResponse();
        Notification::assertSentTo($user, PasswordForgottenNotification::class);
    }


    /**
     * POST /users/forgot for a Facebook user
     */
    public function testForgotFacebookUser()
    {
        Notification::fake();

        $user = User::random(["settings", "LIKE", "%facebook_id%"]);
        $this->json("post", "/auth/forgot", ["email" => $user->email]);

        $this->assertResponseStatus(400);
        $this->assertOpenAPIResponse();
        Notification::assertNotSentTo($user, PasswordForgottenNotification::class);
    }
}

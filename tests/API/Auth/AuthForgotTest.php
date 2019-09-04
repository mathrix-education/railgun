<?php

namespace Tests\API\Auth;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\APIStatelessTestCase;

/**
 * Class AuthRefreshTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AuthForgotTest extends APIStatelessTestCase
{
    /**
     * POST /users/forgot
     */
    public function testForgot(): void
    {
        Mail::fake();
        $user = User::random(["settings", "NOT LIKE", "%facebook_id%"]);

        $this->json("post", "/auth/forgot", ["email" => $user->email]);

        $this->assertResponseOk();
        $this->assertNotInDatabase("users", ["id" => $user->id, "token" => null]);
        $this->assertOpenAPIResponse($this->response);
        $this->assertMailSentTo(PasswordForgottenMail::class, $user);
    }
}

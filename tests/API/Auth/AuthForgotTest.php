<?php

declare(strict_types=1);

namespace Tests\API\Auth;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\APIStatelessTestCase;
use function factory;

class AuthForgotTest extends APIStatelessTestCase
{
    /**
     * POST /users/forgot
     */
    public function testForgot(): void
    {
        Mail::fake();
        $this->requester = factory(User::class)->create();

        $this->json('post', '/auth/forgot', ['email' => $this->requester->email]);

        $this->assertResponseOk();
        $this->assertNotInDatabase('users', ['id' => $this->requester->id, 'token' => null]);
        $this->assertOpenAPIResponse($this->response);
        $this->assertMailSentTo(PasswordForgottenMail::class, $this->requester);
    }
}

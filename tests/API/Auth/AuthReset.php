<?php

declare(strict_types=1);

namespace Tests\API\Auth;

use App\Mails\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\APIStatelessTestCase;
use function env;

class AuthReset extends APIStatelessTestCase
{
    /**
     * POST /auth/reset/{token}
     */
    public function testReset(): void
    {
        Mail::fake();
        // Set the token to a known value
        $user        = User::random(['settings', 'NOT LIKE', 'facebook_id']);
        $user->token = $this->faker->uuid;
        $user->save();

        $this->json('post', '/auth/reset/{$user->token}', [
            'password' => env('DB_PASSWORD'),
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
        $this->assertMailSentTo(PasswordResetMail::class, $user);
    }
}

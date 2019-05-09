<?php

namespace Tests\API\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\APIStatelessTestCase;

/**
 * Class AuthReset.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AuthResetStatelessTest extends APIStatelessTestCase
{
    public function testReset()
    {
        Notification::fake();
        $user = User::random(["settings", "NOT LIKE", "facebook_id"]);

        User::unguarded(function() use ($user) {
            $user->token = $this->faker->uuid;
            $user->save();
        });

        $this->json("post", "/auth/reset/{$user->token}", [
            "password" => env("DB_PASSWORD")
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}

<?php

namespace Tests\API\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Tests\APITestCase;

/**
 * Class AuthTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AuthLoginTest extends APITestCase
{
    /**
     * POST /auth/login
     */
    public function testLogin()
    {
        $user = User::random();
        $oldHash = $user->password; // Save the current hash for revert after the test
        $newPassword = $this->faker->password;
        $user->password = $newPassword;
        $user->save();

        $this->json("post", "/auth/login", [
            "email" => $user->email,
            "password" => $newPassword
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->debug();

        // Revert password change
        DB::update("UPDATE users SET password = ? WHERE id = ?", [$oldHash, $user->id]);
    }


    /**
     * POST /auth/login
     */
    public function testLoginFailed()
    {
        $user = User::random();

        $this->json("post", "/auth/login", [
            "email" => $user->email,
            "password" => "incorrect-password"
        ]);

        $this->assertOpenAPIResponse();
    }
}

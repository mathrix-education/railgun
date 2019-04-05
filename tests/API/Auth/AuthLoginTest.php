<?php

namespace Tests\API\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Mathrix\Lumen\Exceptions\ValidationException;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API\APITestCase;

/**
 * Class AuthTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class AuthLoginTest extends APITestCase
{
    /**
     * Get a valid passport client
     * @return Client
     */
    private function getValidPassportClient()
    {
        /** @var Client $client */
        $client = Client::query()
            ->where([
                ["password_client", "=", true],
                ["revoked", "=", false]
            ])
            ->firstOrFail();

        return $client;
    }


    /**
     * POST /auth/login
     */
    public function testLogin()
    {
        $client = $this->getValidPassportClient();
        $user = User::random();
        $oldHash = $user->password; // Save the current hash for revert after the test
        $newPassword = $this->faker->password;
        $user->password = $newPassword;
        $user->save();

        $this->json("post", "/auth/login", [
            "grant_type" => "password",
            "client_id" => $client->getAttribute("id"),
            "client_secret" => $client->getAttribute("secret"),
            "username" => $user->email,
            "password" => $newPassword
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        // Revert password change
        DB::update("UPDATE users SET password = ? WHERE id = ?", [$oldHash, $user->id]);
    }


    /**
     * POST /auth/login
     */
    public function testLoginFailed()
    {
        $client = $this->getValidPassportClient();
        $user = User::random();

        $this->json("post", "/auth/login", [
            "grant_type" => "password",
            "client_id" => $client->getAttribute("id"),
            "client_secret" => $client->getAttribute("secret"),
            "username" => $user->email,
            "password" => "incorrect-password"
        ]);

        $this->assertOpenAPIResponse();
    }
}

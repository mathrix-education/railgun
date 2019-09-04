<?php

namespace Tests\API\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\APIStatelessTestCase;

/**
 * Class AuthTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AuthLoginTest extends APIStatelessTestCase
{
    /**
     * POST /auth/login
     */
    public function testLogin(): void
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
        $this->assertOpenAPIResponse($this->response);

        // Revert password change
        DB::table("users")
            ->where("id", "=", $user->id)
            ->update(["password" => $oldHash]);
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

        $this->assertResponseStatus(401);
        $this->assertOpenAPIResponse($this->response);
    }
}

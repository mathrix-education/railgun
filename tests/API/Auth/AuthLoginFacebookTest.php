<?php

namespace Tests\API\Auth;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\Serializer;
use Tests\APIStatefulTestCase;

/**
 * Class AuthLoginFacebook.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.1-rc1
 */
class AuthLoginFacebookTest extends APIStatefulTestCase
{
    private const ACCESS_TOKEN_EXPIRED = "EAAGARIhmrOwBAKcDFRkch1LSoNPzQ6p8Nevmd3gjZBzEENarAQX79ymIkEUQ4VQ8OUMZA7m2Ruc6nLZCkFqZBzMGB3SU5OdKskWOot0YlUSRlfDTMDrZA4BtaZA0ThFhe2lvJenn9Wgx9QFv0egVZApe60uUjYcQhcGXq0iMfH53ARte3vR8mZBvmn5dmgEYkSWnlJJ3xUH0yjAdTYOBHnLW";
    private const ACCESS_TOKEN_VALID = "EAAGARIhmrOwBAGFz66OnS0essQFdaLO6ibBUDIt0LH4TUKfec5Pepjj0xVveqKWdHrNVezHSmzvTTZCpZCs5gUGGtI2PNVBbdTCzsPKkBazn0tRgA8lhlNzvk3ZAZATLJzxbPhxw63g9mA2xfpQzRlUz8tclf7CKNXOUFtpucZCedHVtgeCLjtFp1w56GYUEZD";


    public function makeFacebookRequest(string $accessToken)
    {
        $expiresIn = Carbon::now()->addDays(89)->timestamp;

        $this->json("post", "/auth/login/facebook", [
            "access_token" => $accessToken,
            "expires_in" => $expiresIn
        ]);

        $token = json_decode($this->response->getContent())->token;
        $serializer = app()->make(Serializer::class);
        /** @var JWS $jws */
        $jws = $serializer->unserialize($token);
        $payload = json_decode($jws->getPayload());
        $user = User::query()->findOrFail($payload->sub);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertEquals($expiresIn, $payload->exp);
        $this->assertIsString($user->metadata["facebook"]["id"]);
        $this->assertEquals(self::ACCESS_TOKEN_VALID, Crypt::decrypt($user->metadata["facebook"]["access_token"], false));
        $this->assertEquals($expiresIn, $user->metadata["facebook"]["expires_in"]);

        return [$expiresIn, $payload, $user];
    }


    /**
     * POST /auth/facebook/login
     */
    public function testFacebookRegister()
    {
        $this->makeFacebookRequest(self::ACCESS_TOKEN_VALID);
    }


    /**
     * POST /auth/facebook/login
     *
     * This is exactly the same request, but we will check that there is only one account created.
     *
     * @depends testFacebookRegister
     * @throws Exception
     */
    public function testFacebookLogin()
    {
        $results = $this->makeFacebookRequest(self::ACCESS_TOKEN_VALID);
        /** @var User $user */
        $user = $results[2];
        $count = User::query()
            ->where("metadata->facebook->id", "=", $user->metadata["facebook"]["id"])
            ->count();

        $this->assertEquals(1, $count);

        // Clean-up
        $user->logs()->delete();
        $user->delete();
    }
}

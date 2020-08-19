<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Exception;
use Google_Client;
use Illuminate\Support\Facades\DB;
use JsonException;
use Mockery\MockInterface;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function base64_decode;
use function base64_encode;
use function explode;
use function json_decode;
use function json_encode;
use function random_bytes;
use const JSON_THROW_ON_ERROR;

class LoginWithGoogleTest extends GraphQLTestCase
{
    /**
     * @throws JsonException
     */
    public function testValidIdToken(): void
    {
        $payload = $this->getIdTokenPayload();
        $token   = base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));

        $this->mock(
            Google_Client::class,
            static function (MockInterface $googleClient) use ($token, $payload): void {
                $googleClient->expects('verifyIdToken')
                    ->withArgs([$token])
                    ->andReturns($payload);
            },
        );

        $this->executeLoginWithGoogle($token);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $userId = $this->extractClaim($this->getData('token'), 'sub');

        $this->assertTrue(
            DB::table('users')
                ->where('id', '=', $userId)
                ->where('metadata', 'like', "%{$payload['sub']}%")
                ->exists(),
        );
    }

    /**
     * @return mixed[] A typical Google identity token payload.
     */
    private function getIdTokenPayload(): array
    {
        $firstName = $this->faker->firstName;
        $lastName  = $this->faker->lastName;

        return [
            'sub'         => (string)$this->faker->regexify('/\d{21/'),
            'email'       => $this->faker->email,
            'name'        => "$firstName $lastName",
            'given_name'  => $firstName,
            'family_name' => $lastName,
        ];
    }

    private function executeLoginWithGoogle(string $accessToken): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation ($accessToken: String!) {
                loginWithGoogle(accessToken: $accessToken) {
                    token
                }
            }
            GRAPHQL,
            ['accessToken' => $accessToken],
        );
    }

    /**
     * Extract claim from a stringified JWT.
     *
     * @param string $jwt   The JWT as a string.
     * @param string $claim The claim to extract.
     *
     * @return mixed The claim value or null if the claim does not exists.
     *
     * @throws JsonException
     */
    private function extractClaim(string $jwt, string $claim)
    {
        $parts = explode('.', $jwt);

        $payload = json_decode(
            base64_decode($parts[1], true),
            true, 512, JSON_THROW_ON_ERROR,
        );

        return $payload[$claim] ?? null;
    }

    public function testMalformedIdToken(): void
    {
        $this->executeLoginWithGoogle('eyZZZZZ');

        $this->assertResponseOk();
        $this->assertErrorCode('INVALID_AUTH_TOKEN');
    }

    public function testExpiredIdToken(): void
    {
        $token = 'eyZZZZZ';

        // We need to mock the Google_Client::verifyIdToken() -> return false
        $this->mock(
            Google_Client::class,
            static function (MockInterface $googleClient) use ($token): void {
                $googleClient->expects('verifyIdToken')
                    ->withArgs([$token])
                    ->andReturns(false);
            },
        );

        $this->executeLoginWithGoogle('eyZZZZZ');

        $this->assertResponseOk();
        $this->assertErrorCode('INVALID_AUTH_TOKEN');
    }

    /**
     * @throws JsonException
     */
    public function testValidAuthCode(): void
    {
        $payload = $this->getIdTokenPayload();
        $idToken = json_encode($payload, JSON_THROW_ON_ERROR, 512);
        $token   = '4/' . base64_encode(random_bytes(64));

        $this->mock(
            Google_Client::class,
            static function (MockInterface $googleClient) use ($token, $idToken, $payload): void {
                $googleClient->expects('fetchAccessTokenWithAuthCode')
                    ->withArgs([$token])
                    ->andReturn(['id_token' => $idToken]);

                $googleClient->expects('verifyIdToken')
                    ->withArgs([$idToken])
                    ->andReturns($payload);
            },
        );

        $this->executeLoginWithGoogle($token);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $userId = $this->extractClaim($this->getData('token'), 'sub');

        $this->assertTrue(
            DB::table('users')
                ->where('id', '=', $userId)
                ->where('metadata', 'like', "%{$payload['sub']}%")
                ->exists(),
        );
    }

    /**
     * @throws Exception
     */
    public function testInvalidAuthCode(): void
    {
        $this->executeLoginWithGoogle('4/' . base64_encode(random_bytes(64)));

        $this->assertResponseOk();
        $this->assertErrorCode('INVALID_AUTH_TOKEN');
    }
}

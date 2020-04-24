<?php

declare(strict_types=1);

namespace Tests\Functional\GraphQL\Mutations;

use Exception;
use Google_Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Mockery\MockInterface;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\GraphQL\Mutations\LoginWithGoogle;
use Railgun\Models\User;
use Tests\Functional\GraphQL\GraphQLFunctionalTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @testdox Mutation loginWithGoogle
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithGoogle
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginWithGoogleTest extends GraphQLFunctionalTestCase
{
    private string $accessToken;
    private int    $userId;

    /**
     * Initialize the common variables.
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->accessToken = base64_encode(random_bytes(64));
        $this->userId      = $this->faker->numberBetween(1, 100);
    }

    /**
     * Mock the Google Client API container instance
     */
    private function mockPayloadResponseSuccess(): void
    {
        $this->mock(Google_Client::class, function (MockInterface $googleClient): void {
            $payload = [
                'sub'         => base64_encode(random_bytes(64)),
                'email'       => $this->faker->email,
                'name'        => $this->faker->name,
                'given_name'  => $this->faker->firstName,
                'family_name' => $this->faker->lastName,
            ];

            $googleClient->expects('verifyIdToken')
                ->withArgs([$this->accessToken])
                ->andReturns($payload);
        });
    }

    /**
     * @testdox creates a new user when there is no associated Google id.
     * @covers ::__construct
     * @covers ::__invoke
     *
     * @throws Exception
     */
    public function testRegisterSuccess(): void
    {
        // Mock Google Client API
        $this->mockPayloadResponseSuccess();

        // Mock User
        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->orWhere->first')->withNoArgs()->andReturnNull();
        $user->expects('save')->withNoArgs()->andReturnNull();
        $user->expects('getKey')->withNoArgs()->andReturns($this->userId);

        // Call the mutation
        $data = $this->invoke(LoginWithGoogle::class, [
            'accessToken' => $this->accessToken,
        ]);

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox login an existing user when there is associated Google id.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginSuccess(): void
    {
        // Mock Google Client API
        $this->mockPayloadResponseSuccess();

        // Mock User
        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->orWhere->first')->withNoArgs()->andReturns($user);
        $user->expects('getKey')->withNoArgs()->andReturns($this->userId);
        $user->scopes = ['a', 'b'];

        // Call the mutation
        $data = $this->invoke(LoginWithGoogle::class, [
            'accessToken' => $this->accessToken,
        ]);

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox throws an error when providing an invalid Google token.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvalidToken(): void
    {
        $this->expectException(InvalidAuthTokenException::class);

        $this->mock(Google_Client::class, static function (MockInterface $googleClient): void {
            $googleClient->expects('verifyIdToken')
                ->withAnyArgs()
                ->andReturns(false);
        });

        // Call the mutation
        $this->invoke(LoginWithGoogle::class, [
            'accessToken' => $this->accessToken,
        ]);
    }
}

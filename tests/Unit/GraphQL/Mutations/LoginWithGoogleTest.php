<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Exception;
use Google_Client;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\InvalidAuthToken;
use Railgun\GraphQL\Mutations\LoginWithGoogle;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @testdox Mutation loginWithGoogle
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithGoogle
 * @uses                \Mathrix\Lumen\JWT\Drivers\Driver
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginWithGoogleTest extends BaseTestCase
{
    private string $accessToken;
    private int $userId;

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
     * Execute the LoginWithGoogle mutation.
     *
     * @return mixed
     */
    private function execute()
    {
        $mutation = $this->app->make(LoginWithGoogle::class);

        return $mutation(
            null,
            ['accessToken' => $this->accessToken],
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );
    }

    /**
     * Mock the Google Client API container instance
     */
    private function mockPayloadResponseSuccess(): void
    {
        $this->mock(Google_Client::class, function (MockInterface $googleClient) {
            $payload = [
                'sub' => base64_encode(random_bytes(64)),
                'email' => $this->faker->email,
                'name' => $this->faker->name,
                'given_name' => $this->faker->firstName,
                'family_name' => $this->faker->lastName,
            ];

            $googleClient->expects('verifyIdToken')
                ->withArgs([$this->accessToken])
                ->andReturns($payload);
        });
    }

    /**
     * @testdox create a new user when there is no associated Google id.
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
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->first')->withNoArgs()->andReturnNull();
        $user->expects('save')->withNoArgs()->andReturnNull();
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId);

        // Call the mutation
        $data = $this->execute();

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
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->first')->withNoArgs()->andReturns($user);
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId);

        // Call the mutation
        $data = $this->execute();

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox throws an error when providing an invalid Google token.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testInvalidToken(): void
    {
        $this->expectException(InvalidAuthToken::class);

        $this->mock(Google_Client::class, static function (MockInterface $googleClient) {
            $googleClient->expects('verifyIdToken')
                ->withAnyArgs()
                ->andReturns(false);
        });

        $this->execute();
    }
}

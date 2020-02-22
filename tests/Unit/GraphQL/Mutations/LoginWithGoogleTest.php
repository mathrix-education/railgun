<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Exception;
use Google_Client;
use Google_Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\GraphQL\Mutations\LoginWithGoogle;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function base64_encode;
use function random_bytes;

/**
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
     * Execute the LoginWithFacebook mutation.
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
     *
     * @param int $accessToken
     */
    private function mockPayloadResponseSuccess(string $accessToken): void
    {
        $this->mock(Google_Client::class, static function (MockInterface $googleClient) use ($accessToken) {
            $payload = [
                'sub' => base64_encode(random_bytes(64)),
                'email' => 'test@test.test',
                'name' => 'test',
                'given_name' => 'test',
                'family_name' => 'test',
            ];

            $googleClient->expects('verifyIdToken')
                ->withArgs([$accessToken])
                ->andReturns($payload);
        });
    }

    /**
     * @testdox create a new user when there is no associated Facebook id.
     * @covers ::__construct
     * @covers ::__invoke
     *
     * @throws Exception
     */
    public function testRegisterSuccess(): void
    {
        //Mock Google Client API
        $this->mockPayloadResponseSuccess($this->accessToken);

        //Mock User
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->first')->withNoArgs()->andReturnNull()->once();
        $user->expects('save')->withNoArgs()->andReturnNull()->once();
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId)->once();

        // Call the mutation
        $data = $this->execute();

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox login an existing user when there is associated Facebook id.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginSuccess(): void
    {
        //Mock Google Client API
        $this->mockPayloadResponseSuccess($this->accessToken);

        // Mock User
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->first')->withNoArgs()->andReturns($user)->once();
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId)->once();

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
        $this->expectException(LogicException::class);

        $this->mock(Google_Client::class, static function (MockInterface $googleClient) {
            $googleClient->expects('verifyIdToken')
                ->withAnyArgs()
                ->andThrows(new LogicException());
        });

        $this->execute();
    }
}

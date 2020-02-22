<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Exception;
use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphUser;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\InvalidAuthToken;
use Railgun\GraphQL\Mutations\LoginWithFacebook;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithFacebook
 * @uses                \Mathrix\Lumen\JWT\Drivers\Driver
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginWithFacebookTest extends BaseTestCase
{
    private string $accessToken;
    private int    $userId;
    private int    $facebookId;

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
        $this->facebookId  = $this->faker->randomNumber(5);
    }

    /**
     * Execute the LoginWithFacebook mutation.
     *
     * @return mixed
     */
    private function execute()
    {
        $mutation = $this->app->make(LoginWithFacebook::class);

        return $mutation(
            null,
            ['accessToken' => $this->accessToken],
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );
    }

    /**
     * Mock the Facebook SDK container instance.
     *
     * @param MockInterface|null $graphUser
     */
    private function mockFacebook(?MockInterface $graphUser = null): void
    {
        $this->mock(Facebook::class, function (MockInterface $facebook) use ($graphUser) {
            // Make the FacebookResponse
            $response = Mockery::mock();
            $response->expects('getGraphUser')->andReturns($graphUser);

            $facebook->expects('get')
                ->withArgs([LoginWithFacebook::GET_ME_URL, $this->accessToken])
                ->andReturns($response);
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
        // Mock GraphUser
        $graphUser = Mockery::mock(GraphUser::class);
        $graphUser->expects('getId')->withNoArgs()->andReturns($this->facebookId)->twice();
        $graphUser->expects('getEmail')->withNoArgs()->andReturns($this->faker->email);
        $graphUser->expects('getFirstName')->withNoArgs()->andReturns($this->faker->firstName);
        $graphUser->expects('getLastName')->withNoArgs()->andReturns($this->faker->lastName);

        // Mock Facebook
        $this->mockFacebook($graphUser);

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
     * @testdox login an existing user when there is associated Facebook id.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginSuccess(): void
    {
        // Mock GraphUser
        $graphUser = Mockery::mock(GraphUser::class);
        $graphUser->expects('getId')->withNoArgs()->andReturns($this->facebookId);

        // Mock User
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->first')->withNoArgs()->andReturns($user);
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId);

        // Mock Facebook
        $this->mockFacebook($graphUser);

        // Call the mutation
        $data = $this->execute();

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox throws an error when providing an invalid Facebook token.
     * @covers ::__construct
     * @covers ::__invoke
     * @uses    \Railgun\Exceptions\InvalidAuthToken
     */
    public function testInvalidToken(): void
    {
        $this->expectException(InvalidAuthToken::class);

        $this->mock(Facebook::class, static function (MockInterface $facebook) {
            $facebook->expects('get')
                ->withAnyArgs()
                ->andThrows(new FacebookAuthenticationException());
        });

        $this->execute();
    }
}

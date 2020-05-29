<?php

declare(strict_types=1);

namespace Tests\Functional\GraphQL\Mutations;

use Exception;
use Facebook\Exceptions\FacebookAuthenticationException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Mockery\MockInterface;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\GraphQL\Mutations\LoginWithFacebook;
use Railgun\Models\User;
use Tests\Functional\GraphQL\GraphQLFunctionalTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @testdox Mutation loginWithFacebook
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithFacebook
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginWithFacebookTest extends GraphQLFunctionalTestCase
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
     * Mock the Facebook SDK container instance.
     *
     * @param MockInterface|null $graphUser The Facebook GraphUser.
     */
    private function mockFacebook(?MockInterface $graphUser = null): void
    {
        $this->mock(Facebook::class, function (MockInterface $facebook) use ($graphUser): void {
            // Make the FacebookResponse
            $response = Mockery::mock();
            $response->expects('getGraphUser')->andReturns($graphUser);

            $facebook->expects('get')
                ->withArgs([LoginWithFacebook::GET_ME_URL, $this->accessToken])
                ->andReturns($response);
        });
    }

    /**
     * @testdox creates a new user when there is no associated Facebook id
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
        $graphUser->expects('getEmail')->withNoArgs()->andReturns($this->faker->email)->twice();
        $graphUser->expects('getFirstName')->withNoArgs()->andReturns($this->faker->firstName);
        $graphUser->expects('getLastName')->withNoArgs()->andReturns($this->faker->lastName);

        // Mock Facebook
        $this->mockFacebook($graphUser);

        // Mock User
        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->orWhere->first')->withNoArgs()->andReturnNull();
        $user->expects('save')->withNoArgs()->andReturnNull();
        $user->expects('getKey')->withNoArgs()->andReturns($this->userId);

        // Call the mutation
        $data = $this->invoke(LoginWithFacebook::class, [
            'accessToken' => $this->accessToken,
        ]);

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox login an existing user when there is associated Facebook id
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginSuccess(): void
    {
        // Mock GraphUser
        $graphUser = Mockery::mock(GraphUser::class);
        $graphUser->expects('getId')->withNoArgs()->andReturns($this->facebookId);
        $graphUser->expects('getEmail')->withNoArgs()->andReturns($this->faker->email);

        // Mock Facebook
        $this->mockFacebook($graphUser);

        // Mock User
        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);
        $user->expects('query->where->orWhere->first')->withNoArgs()->andReturns($user);
        $user->expects('getKey')->withNoArgs()->andReturns($this->userId);
        $user->permissions = ['a', 'b'];

        // Call the mutation
        $data = $this->invoke(LoginWithFacebook::class, [
            'accessToken' => $this->accessToken,
        ]);

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox throws an error when providing an invalid Facebook token
     * @covers ::__construct
     * @covers ::__invoke
     * @uses    \Railgun\Exceptions\InvalidAuthTokenException
     */
    public function testInvalidToken(): void
    {
        $this->expectException(InvalidAuthTokenException::class);

        $this->mock(Facebook::class, static function (MockInterface $facebook): void {
            $facebook->expects('get')
                ->withAnyArgs()
                ->andThrows(new FacebookAuthenticationException());
        });

        $this->invoke(LoginWithFacebook::class, [
            'accessToken' => $this->accessToken,
        ]);
    }
}

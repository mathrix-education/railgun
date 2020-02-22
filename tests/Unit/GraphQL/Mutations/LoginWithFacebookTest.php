<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Facebook\Facebook;
use Facebook\GraphNodes\GraphUser;
use GraphQL\Type\Definition\ResolveInfo;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\GraphQL\Mutations\LoginWithFacebook;
use Tests\BaseTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithFacebook
 */
class LoginWithFacebookTest extends BaseTestCase
{
    public function testInvoke(): void
    {
        $this->mock(Facebook::class, function (MockInterface $facebook) {
            // Make the GraphUser
            $graphUser = Mockery::mock(GraphUser::class);
            $graphUser->expects('getFirstName')
                ->withNoArgs()
                ->andReturns($this->faker->firstName)
                ->once();
            $graphUser->expects('getLastName')
                ->withNoArgs()
                ->andReturns($this->faker->lastName)
                ->once();
            $graphUser->expects('getEmail')
                ->withNoArgs()
                ->andReturns($this->faker->email)
                ->once();
            $graphUser->expects('getId')
                ->withNoArgs()
                ->andReturns(base64_encode(random_bytes(64)))
                ->twice();

            // Make the FacebookResponse
            $response = Mockery::mock();
            $response->expects('getGraphUser')->andReturns($graphUser);

            $facebook->expects('get')
                ->withAnyArgs()
                ->andReturns($response);
        });

        $instance = $this->app->make(LoginWithFacebook::class);

        $instance(
            null,
            ['accessToken' => 'test'],
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );
    }
}

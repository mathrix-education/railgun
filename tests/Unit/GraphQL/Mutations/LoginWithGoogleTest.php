<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Google_Client;
use GraphQL\Type\Definition\ResolveInfo;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\GraphQL\Mutations\LoginWithGoogle;
use Tests\BaseTestCase;
use function base64_encode;
use function random_bytes;

/**
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginWithGoogle
 */
class LoginWithGoogleTest extends BaseTestCase
{
    public function testInvoke(): void
    {
        $this->mock(Google_Client::class, static function (MockInterface $googleClient) {
            $payload = [];

            $googleClient->expects('verifyIdToken')
                ->withArgs([base64_encode(random_bytes(64))])
                ->andReturns($payload);
        });

        $instance = $this->app->make(LoginWithGoogle::class);

        $instance(
            null,
            ['accessToken' => 'test'],
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );
    }
}

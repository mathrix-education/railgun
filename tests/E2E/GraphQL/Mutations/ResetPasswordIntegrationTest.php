<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Crypto\PasswordResetCrypto;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation resetPassword
 */
class ResetPasswordIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation($token: String!, $newPassword: String!) {
        resetPassword(token: $token, newPassword: $newPassword)
    }
    GRAPHQL;

    /**
     * @testdox resets password using a valid token
     *
     * @throws BindingResolutionException
     */
    public function testValidToken(): void
    {
        $this->app->make('config')->set('app.debug', false); // Disable debug

        $oldPassword = $this->faker->password;
        $newPassword = $this->faker->password;

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->update(['password' => $oldPassword]);

        /** @var PasswordResetCrypto $crypto */
        $crypto = $this->app->make(PasswordResetCrypto::class);
        $token  = $crypto->get($user);

        $this->graphQL(self::QUERY, ['token' => $token, 'newPassword' => $newPassword]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('resetPassword'));

        // Assert that the password now works
        $user->refresh();
        $this->assertFalse(Hash::check($oldPassword, $user->password));
        $this->assertTrue(Hash::check($newPassword, $user->password));

        // Assert that token is not valid anymore
        $this->expectException(InvalidAuthTokenException::class);
        $crypto->check($token);
    }

    /**
     * @testdox resets the password of the targeted user in debug mode
     */
    public function testValidTokenDebugMode(): void
    {
        $this->app->make('config')->set('app.debug', true); // Enable debug

        $oldPassword = $this->faker->password;
        $newPassword = $this->faker->password;

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->update(['password' => $oldPassword]);

        $this->graphQL(self::QUERY, ['token' => $user->id, 'newPassword' => $newPassword]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('resetPassword'));

        // Assert that the password now works
        $user->refresh();
        $this->assertFalse(Hash::check($oldPassword, $user->password));
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }
}

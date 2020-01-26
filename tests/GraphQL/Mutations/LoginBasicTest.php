<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use Railgun\Models\User;
use Jose\Component\Signature\Serializer\Serializer;
use Mathrix\Lumen\JWT\Auth\JWT\JWTVerifier;
use Tests\GraphQLTestCase;
use const JSON_THROW_ON_ERROR;
use function app;
use function env;
use function json_decode;

/**
 * @testdox Mutation LoginBasic
 */
class LoginBasicTest extends GraphQLTestCase
{
    private const QUERY = <<<GRAPHQL
    mutation (\$email: String!, \$password: String!) {
        loginBasic(email: \$email, password: \$password) {
            token
        }
    }
    GRAPHQL;

    /**
     * @testdox returns a JWT on success
     */
    public function testLoginBasicSuccess(): void
    {
        $user = User::random();

        $this->graphQL(self::QUERY, [
            'email'    => $user->email,
            'password' => env('DB_PASSWORD'),
        ]);

        $token = $this->getContent('data.loginBasic.token');
        /** @var JWTVerifier $verifier */
        $verifier = app()->make(JWTVerifier::class);
        /** @var Serializer $serializer */
        $serializer = app()->make(Serializer::class);
        $payload    = json_decode($serializer->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseOk();
        $this->assertNull($this->getErrors());
        $this->assertNotNull($token);
        $this->assertTrue($verifier->verify($token));
        $this->assertEquals($payload->sub, $user->id);
    }

    /**
     * @testdox fails with invalid credentials error on non-existing email
     */
    public function testLoginBasicFailsOnNonExistingEmail(): void
    {
        $this->graphQL(self::QUERY, [
            'email'    => 'idontexist',
            'password' => env('DB_PASSWORD'),
        ]);

        $this->assertResponseOk();
        $this->assertNotNull($this->getErrors());
        $this->assertEquals($this->getContent('errors.0.message'), 'The given credentials were invalid');
    }

    /**
     * @testdox fails with invalid credentials error on invalid password
     */
    public function testLoginBasicFailsOnInvalidPassword(): void
    {
        $this->graphQL(self::QUERY, [
            'email'    => User::random()->email,
            'password' => env('DB_PASSWORD') . 'invalid',
        ]);

        $this->assertResponseOk();
        $this->assertNotNull($this->getErrors());
        $this->assertEquals($this->getContent('errors.0.message'), 'The given credentials were invalid');
    }
}

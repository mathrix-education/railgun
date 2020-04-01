<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Mutations;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Railgun\Exceptions\InvalidCredentialsException;
use Railgun\GraphQL\Mutations\LoginBasic;
use Railgun\Models\User;
use Tests\Unit\GraphQLUnitTestCase;

/**
 * @testdox [Unit] Mutation loginBasic
 * @coversDefaultClass \Railgun\GraphQL\Mutations\LoginBasic
 * @uses                \Mathrix\Lumen\JWT\Drivers\Driver
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LoginBasicTest extends GraphQLUnitTestCase
{
    private int    $userId;
    private string $email;
    private string $password;

    public function setUp(): void
    {
        parent::setUp();

        $this->userId   = $this->faker->numberBetween(1, 300);
        $this->email    = $this->faker->email;
        $this->password = $this->faker->password;
    }

    /**
     * @testdox login an existing user with valid credentials.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginSuccess(): void
    {
        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);

        $user->email    = $this->email;
        $user->password = Hash::make($this->password);

        $user->expects('query->where->get->first')
            ->withNoArgs()
            ->andReturns($user);
        $user->expects('getAuthIdentifier')->withAnyArgs()->andReturns($this->userId);

        $data = $this->invoke(LoginBasic::class, [
            'email'    => $this->email,
            'password' => $this->password,
        ]);

        $this->assertNotEmpty($data['token']);
    }

    /**
     * @testdox throws a InvalidCredentials exception when the user email cannot be found.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginEmailNotFound(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);

        $user->expects('query->where->get->first')->withNoArgs();

        $this->invoke(LoginBasic::class, [
            'email'    => $this->email,
            'password' => $this->password,
        ]);
    }

    /**
     * @testdox throws a InvalidCredentials exception when the user password does not match the saved hash.
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testLoginInvalidPassword(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        /** @var User|MockInterface $user */
        $user = Mockery::mock('overload:' . User::class, Authenticatable::class);

        $user->email    = $this->email;
        $user->password = Hash::make($this->password);

        $user->expects('query->where->get->first')
            ->withNoArgs()
            ->andReturns($user);

        $this->invoke(LoginBasic::class, [
            'email'    => $this->email,
            'password' => $this->faker->password,
        ]);
    }
}

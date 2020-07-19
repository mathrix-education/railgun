<?php

declare(strict_types=1);

namespace Tests\Functional\Crypto;

use JsonException;
use Railgun\Crypto\PasswordResetCrypto;
use Railgun\Exceptions\InvalidAuthTokenException;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use Windy\Guardian\Guardian;
use function explode;
use function hash;
use function hash_equals;
use function json_decode;
use function preg_replace;
use function str_shuffle;
use const JSON_THROW_ON_ERROR;

/**
 * @coversDefaultClass \Railgun\Crypto\PasswordResetCrypto
 */
class PasswordResetCryptoTest extends ApplicationTestCase
{
    private PasswordResetCrypto $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->instance = $this->app->make(PasswordResetCrypto::class);
    }

    /**
     * @covers ::get
     *
     * @throws JsonException
     */
    public function testGet(): void
    {
        $user    = User::random();
        $token   = $this->instance->get($user);
        $payload = json_decode(
            Guardian::get('reset')->unserialize($token)->getPayload(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertArrayHasKey('hsh', $payload);
        $hash = $payload['hsh'];
        $this->assertTrue(hash_equals(hash('sha256', $user->password), $hash));
    }

    /**
     * @covers ::check
     */
    public function testCheckValidToken(): void
    {
        $user    = User::random();
        $token   = $this->instance->get($user);
        $checked = $this->instance->check($token);

        $this->assertTrue($user->is($checked));
    }

    /**
     * @covers ::check
     */
    public function testCheckInvalidSignature(): void
    {
        $token     = $this->instance->get(User::random());
        $signature = str_shuffle(explode('.', $token)[2]);
        $replace   = '$1.$2.' . $signature;
        $invalid   = preg_replace('/^(.*)\.(.*)\.(.*)$/', $replace, $token);

        $this->expectException(InvalidAuthTokenException::class);
        $this->instance->check($invalid);
    }

    /**
     * @covers ::check
     */
    public function testCheckPasswordChangedToken(): void
    {
        $user  = User::random();
        $token = $this->instance->get($user);

        $user->password = $this->faker->password;
        $user->save();

        $this->expectException(InvalidAuthTokenException::class);
        $this->instance->check($token);
    }
}

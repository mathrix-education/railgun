<?php

declare(strict_types=1);

namespace Tests\Unit\Mails;

use Illuminate\Contracts\Container\BindingResolutionException;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use JsonException;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Mathrix\Lumen\JWT\Utils\JWTConfig;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use Tests\BaseTestCase;
use const JSON_THROW_ON_ERROR;
use function explode;
use function factory;
use function hash;
use function json_decode;
use function strlen;

/**
 * @coversDefaultClass \Railgun\Mails\PasswordForgottenMail
 */
class PasswordForgottenMailTest extends BaseTestCase
{
    /**
     * @throws BindingResolutionException
     * @throws InvalidClaimException
     * @throws MissingMandatoryClaimException
     * @throws JsonException
     */
    public function testConstruct(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $mail = new PasswordForgottenMail($user);

        $this->assertIsString($mail->firstName);
        $this->assertIsString($mail->resetLink);
        $this->assertLessThan(2000, strlen($mail->resetLink));

        $driver  = Driver::from(JWTConfig::key('reset'), JWTConfig::payload('reset'));
        $token   = explode('?token=', $mail->resetLink)[1];
        $jws     = $driver->unserialize($token);
        $payload = json_decode($jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(1, $jws->countSignatures());
        $this->assertTrue($driver->check($jws));
        $this->assertTrue($driver->verify($jws));
        $this->assertEquals($user->uuid, $payload['sub']);
        $this->assertEquals(hash('sha256', $user->password), $payload['hsh']);
    }
}

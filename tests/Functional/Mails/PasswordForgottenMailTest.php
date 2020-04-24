<?php

declare(strict_types=1);

namespace Tests\Functional\Mails;

use JsonException;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use Windy\Guardian\Guardian;
use function explode;
use function factory;
use function hash;
use function json_decode;
use function strlen;
use const JSON_THROW_ON_ERROR;

/**
 * @coversDefaultClass \Railgun\Mails\PasswordForgottenMail
 */
class PasswordForgottenMailTest extends ApplicationTestCase
{
    /**
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

        $authority = Guardian::get('reset');
        $token     = explode('?token=', $mail->resetLink)[1];
        $jws       = $authority->unserialize($token);
        $payload   = json_decode($jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(1, $jws->countSignatures());
        $this->assertTrue($authority->check($jws));
        $this->assertTrue($authority->verify($jws));
        $this->assertEquals($user->uuid, $payload['sub']);
        $this->assertEquals(hash('sha256', $user->password), $payload['hsh']);
    }
}

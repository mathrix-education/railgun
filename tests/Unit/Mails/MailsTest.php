<?php

declare(strict_types=1);

namespace Tests\Unit\Mails;

use App\Mails\BaseMail;
use App\Registrars\MailsRegistrar;
use Tests\APIStatelessTestCase;

/**
 * Test that the Mail render correctly.
 */
class MailsTest extends APIStatelessTestCase
{
    public function mailsDataProvider(): array
    {
        $mails = MailsRegistrar::getMails();
        $data  = [];
        foreach ($mails as $mail) {
            $data[$mail] = [$mail];
        }

        return $data;
    }

    /**
     * @param string|BaseMail $mailClass
     *
     * @dataProvider mailsDataProvider
     */
    public function testMailsRender(string $mailClass): void
    {
        $this->assertInstanceOf($mailClass, $mailClass::mock());
    }
}

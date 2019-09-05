<?php

namespace Tests\API;

use App\Mails\BaseMail;
use App\Registrars\MailsRegistrar;
use Illuminate\Support\Facades\Facade;
use Tests\APIStatelessTestCase;

/**
 * Class MailsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class MailsTest extends APIStatelessTestCase
{
    public function mails()
    {
        $mails = MailsRegistrar::getMails();
        $data = [];

        foreach ($mails as $mail) {
            $data[$mail] = [$mail];
        }

        return $data;
    }


    /**
     * @param string|BaseMail $mailClass
     *
     * @dataProvider mails
     */
    public function testMailsRender(string $mailClass)
    {
        $this->assertInstanceOf($mailClass, $mailClass::mock());
    }
}

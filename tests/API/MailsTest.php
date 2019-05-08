<?php

namespace Tests\API;

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
    /**
     * Test that all emails produce valid views i.e. that there is no syntax errors.
     * This is better that nothing, but it does not guarantees that the mail is correct.
     */
    public function testAllMails()
    {
        MailsRegistrar::getMails()->map(function ($mailableClass, string $uri) {
            $this->json("get", $uri);
            $this->assertResponseOk();

            /**
             * Needed for multiple requests in the same test.
             * @see https://stackoverflow.com/a/38722555/3728261
             */
            Facade::clearResolvedInstances();
        });
    }
}

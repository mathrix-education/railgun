<?php

namespace Tests\API;

use Tests\APIStatelessTestCase;

/**
 * Class BadgesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class BadgesStatelessTest extends APIStatelessTestCase
{
    /**
     * GET /badges/version
     */
    public function testVersion()
    {
        $this->json("get", "/badges/version");
        $this->assertResponseOk();
    }
}

<?php

namespace Tests\API;

use Tests\APITestCase;

/**
 * Class BadgesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class BadgesTest extends APITestCase
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

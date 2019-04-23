<?php

namespace Tests\API;

use Tests\APITestCase;

/**
 * Class SystemTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class SystemTest extends APITestCase
{
    /**
     * GET /
     */
    public function testBase()
    {
        $this->json("get", "/");

        $this->assertResponseOk();
    }


    /**
     * GET /system/health
     */
    public function testHealth()
    {
        $this->json("get", "/system/health");

        $this->assertResponseOk();
    }

    /**
     * GET /system/sitemap
     */
    public function testSitemap()
    {
        $this->json("get", "/system/sitemap");

        $this->assertResponseOk();
    }
}

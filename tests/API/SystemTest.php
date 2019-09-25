<?php

declare(strict_types=1);

namespace Tests\API;

use Tests\APIStatelessTestCase;

class SystemTest extends APIStatelessTestCase
{
    /**
     * GET /system/health
     */
    public function testHealth()
    {
        $this->json('get', '/system/health');

        $this->assertResponseOk();
    }

    /**
     * GET /system/sitemap
     */
    public function testSitemap()
    {
        $this->json('get', '/system/sitemap');

        $this->assertResponseOk();
    }
}

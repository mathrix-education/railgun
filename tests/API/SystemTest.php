<?php

declare(strict_types=1);

namespace Tests\API;

use Tests\APIStatelessTestCase;

class SystemTest extends APIStatelessTestCase
{
    /**
     * GET /system/sitemap
     */
    public function testSitemap(): void
    {
        $this->json('get', '/system/sitemap');

        $this->assertResponseOk();
    }
}

<?php

namespace Tests\API\Content;

/**
 * Class ContentAsAdminTest
 * Test content as admin.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class ContentAsAdminTest extends ContentAsModeratorTest
{
    public function testContentRoutes()
    {
        $this->json("get", "/content/routes");

        $this->assertHttp200Success();
        $this->debug();
    }
}

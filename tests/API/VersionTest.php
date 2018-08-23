<?php

namespace Tests\API;

/**
 * Class VersionTest.
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class VersionTest extends APITestCase
{
    /**
     * GET /.
     */
    public function testVersion()
    {
        $this->json("get", "/");

        $this->assertJsonKeys(["message", "build"]);
    }
}

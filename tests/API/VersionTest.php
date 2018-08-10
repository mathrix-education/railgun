<?php
namespace Tests\API;

use Tests\BaseTestCase;

/**
 * Class VersionTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\API
 * @since
 * @copyright Mathrix Education SA
 */
class VersionTest extends BaseTestCase
{
    /**
     * GET /
     */
    public function testVersion()
    {
        $this->json("get", "/");

        $this->assertJsonKeys(["message", "commit", "build"]);
    }
}

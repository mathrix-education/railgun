<?php

namespace Tests\API\Paths;

use Tests\API\ScopeTestCase;
use App\Utils\Tree;

/**
 * Class PathsAsPublicTest
 * Test paths as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.2.0
 */
class PathsAsPublicTest extends ScopeTestCase
{
    /**
     * @throws \Exception
     */
    public function testMarkIndex()
    {
        $tree = new Tree();

        $this->json("post", "/paths/index", $tree->flatIds());

        $this->assertHttp401Unauthorized();
    }
}

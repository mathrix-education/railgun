<?php
namespace Tests\API\Paths;

use Tests\API\ScopeTestCase;
use App\Utils\Tree;

/**
 * Class PathsAsPublicTest.
 * Test paths as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Paths
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

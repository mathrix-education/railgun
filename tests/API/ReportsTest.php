<?php

namespace Tests\API;

use Mathrix\Lumen\Tests\REST\Standard\StandardDeleteTest;
use Tests\APIStatelessTestCase;

/**
 * Class ReportsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ReportsTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest, StandardDeleteTest;

    public function testPost()
    {
        $this->assertRestPostSuccess(null, ["status" => "todo"]);
    }
}

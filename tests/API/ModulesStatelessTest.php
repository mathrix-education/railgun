<?php

namespace Tests\API;

use App\Models\Module;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APIStatelessTestCase;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ModulesStatelessTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;


    /**
     * POST /modules
     */
    public function testPost(): void
    {
        $this->assertRestPostSuccess(null, null, ["subFactory" => "random"]);
    }


    /**
     * DELETE /modules/{moduleId}
     */
    public function testDelete(): void
    {
        $module = Module::random();
        $pivot = Module::random(["id", "!=", $module->id]);

        $module->posts()->update(["module_id" => $pivot->id]);
        $module->products()->update(["module_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $module->id]]);
    }


    /**
     * GET /modules/{moduleId}/comments/{page}/{perPage}
     */
    public function testComments(): void
    {
        $this->assertRestRelationSuccess("comments");
    }

    /**
     * GET /modules/{moduleId}/reports/{page}/{perPage}
     */
    public function testReports(): void
    {
        $this->assertRestRelationSuccess("reports");
    }
}

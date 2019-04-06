<?php

namespace Tests\API;

use App\Models\Activity;
use App\Models\Module;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ModulesTest extends APITestCase
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
    public function testDelete()
    {
        $module = Module::random();
        $pivot = Module::random(["id", "!=", $module->id]);

        $module->posts()->update(["module_id" => $pivot->id]);
        $module->references()->update(["module_id" => $pivot->id]);
        $module->products()->update(["module_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $module->id]]);
    }


    /**
     * GET /modules/by-activity/{activityId}
     */
    public function testByActivity(): void
    {
        $this->assertRestBySuccess(Activity::class);
    }
}

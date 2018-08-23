<?php

namespace Tests\API\Modules;

use App\Models\Activity;
use App\Models\Module;

/**
 * Class ModulesAsModeratorTest
 * Test modules as moderator.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 1.0.0
 */
class ModulesAsModeratorTest extends ModulesAsRedactorTest
{
    /**
     * POST /modules.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAdd();
        $this->assertHttp200Success();
    }

    /**
     * PATCH /modules/{id}.
     *
     * @throws \Exception
     */
    public function testEditWithActivities()
    {
        $activityIds = Activity::inRandomOrder()->limit(3)->get(["id"])->pluck("id");

        $this->genericEdit([
            "link" => [
                "activities" => $activityIds,
            ],
        ]);

        foreach ($activityIds as $activityId) {
            $this->assertInDatabase("activity_module", [
                "activity_id" => $activityId,
                "module_id" => $this->editModelId,
            ]);
        }
    }

    /**
     * DELETE /modules/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_module", ["module_id" => $this->deleteModelId]);
    }

    /**
     * GET /modules/{id} with a premium module as non premium user
     * Moderator privileges overrides the premium limitation.
     *
     * @throws \Exception
     */
    public function testGetPremiumAsNonPremium()
    {
        /** @var Module $module */
        $module = Module::wherePremium(true)->inRandomOrder()->firstOrFail();
        $this->get("/modules/{$module->id}");

        $response = $this->getResponseData();
        $this->assertTrue($response->premium);
        $this->assertTrue(!empty($response->type));
    }
}

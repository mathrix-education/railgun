<?php

namespace Tests\API;

use App\Models\Activity;
use App\Models\Module;
use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class ModulesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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


    /**
     * GET /modules/by-activity/{activityId} with premium activity as premium user
     */
    public function testByActivityPremium(): void
    {
        $user = User::random();
        $user->premium_bypass = true;
        $user->save();
        $module = Module::random(["visible", "=", 1]);
        $activity = $module->activity;
        $activity->premium = true;
        $activity->save();

        JWT::actingAs($user);
        $this->json("get", "/modules/by-activity/{$activity->id}/0/1000");

        $this->assertOpenAPIResponse("/modules/by-activity/{activityId}/{page}/{perPage}");
    }
}

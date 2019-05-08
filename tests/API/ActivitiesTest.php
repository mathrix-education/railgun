<?php

namespace Tests\API;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APIStatelessTestCase;

/**
 * Class ActivitiesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ActivitiesTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * DELETE /activities/{activityId}
     */
    public function testDelete(): void
    {
        $activity = Activity::random();
        $pivot = Activity::random(["id", "!=", $activity->id]);

        $activity->modules()->update(["activity_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $activity->id]]);
    }


    /**
     * GET /activities/{activityId}/modules/{page}/{perPage}
     */
    public function testModules(): void
    {
        $this->assertRestRelationSuccess("modules");
    }


    /**
     * PATCH /activities/{activityId}/mark
     */
    public function testMark(): void
    {
        $activity = Activity::random(["visible", "=", true]);
        $uri = "/activities/{$activity->id}/mark";
        $status = $this->faker->randomElement(Mark::STATUSES);

        /** @var User $user */
        $user = JWT::autoScope("patch", $uri);
        $this->json("patch", $uri, [
            "status" => $status
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertInDatabase("marks", [
            "status" => $status,
            "user_id" => $user->id,
            "markable_type" => "activity",
            "markable_id" => $activity->id
        ]);
    }


    /**
     * PATCH /activities/{activityId}/unmark
     */
    public function testUnmark(): void
    {
        $mark = Mark::random(["markable_type", "=", "activity"]);
        JWT::actingAs($mark->user);

        $this->json("patch", "/activities/{$mark->markable_id}/unmark");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertNotInDatabase("marks", [
            "user_id" => $mark->user_id,
            "markable_type" => "activity",
            "markable_id" => $mark->markable_id
        ]);
    }
}

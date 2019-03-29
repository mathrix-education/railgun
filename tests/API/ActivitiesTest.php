<?php

namespace Tests\API;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\Notion;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;
use Tests\API\APITestCase;

/**
 * Class ActivitiesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ActivitiesTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;


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
     * GET /activities/by-notions/{notionId}
     */
    public function testByNotion(): void
    {
        $this->assertRestBySuccess(Notion::class);
    }


    /**
     * PATCH /activities/{activityId}/mark
     */
    public function testMark(): void
    {
        $activity = Activity::random(["visible", "=", true]);
        $uri = "/activities/{$activity->id}/mark";
        $status = $this->faker->randomElement(Mark::STATUSES);

        $this->autoMockScope("patch", $uri);
        $this->json("patch", $uri, [
            "status" => $status
        ]);

        $this->assertResponseOk();
        $this->assertInDatabase("marks", [
            "status" => $status,
            "user_id" => $this->passportUser->id,
            "markable_type" => "activity",
            "markable_id" => $activity->id
        ]);
        $this->assertJsonResponseMatches([
            "mark" => $status
        ]);
    }


    /**
     * PATCH /activities/{activityId}/unmark
     */
    public function testUnmark(): void
    {
        $mark = Mark::random(["markable_type", "=", "activity"]);
        Passport::actingAs($mark->user, ["registered"]);

        $this->json("patch", "/activities/{$mark->markable_id}/unmark");

        $this->assertResponseOk();
        $this->assertNotInDatabase("marks", [
            "user_id" => $mark->user_id,
            "markable_type" => "activity",
            "markable_id" => $mark->markable_id
        ]);
        $this->assertJsonResponseMatches([
            "mark" => null
        ]);
    }
}

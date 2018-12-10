<?php

namespace Tests\API\Activities;

use App\Models\Mark;
use App\Models\Path;
use Laravel\Passport\Passport;
use App\Utils\Tree;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as registered user.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ActivitiesAsRegisteredTest extends ActivitiesAsPublicTest
{
    /**
     * GET /activities/to-revise.
     *
     * @throws \Exception
     */
    public function testToRevise()
    {
        $mark = Mark::inRandomOrder()
            ->where("status", "=", "to_revise")
            ->firstOrFail();
        $user = $mark->user;
        Passport::actingAs($user, [$this->user->type]);

        $this->get("/activities/to-revise");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "[*].user_id" => $user->id,
            "[*].status" => "to_revise"
        ]);
    }

    /**
     * GET /activities/incomplete.
     *
     * @throws \Exception
     */
    public function testView()
    {
        $mark = Mark::inRandomOrder()
            ->where("status", "=", "view")
            ->firstOrFail();
        $user = $mark->user;
        Passport::actingAs($user, [$this->user->type]);

        $this->get("/activities/view");

        $marks = $this->getResponseData();
        $disciplinesIncluded = [];

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "[*].user_id" => $user->id,
            "[*].status" => "view"
        ]);
        foreach ($marks as $mark) {
            $this->assertNotTrue(\in_array($mark->path->discipline_id, $disciplinesIncluded, true));
            $disciplinesIncluded[] = $mark->path->discipline_id;
        }
    }

    /**
     * Generic mark test.
     *
     * @param $status
     *
     * @throws \Exception
     */
    public function mark($status)
    {
        $tree = new Tree();
        $data = $tree->flatIds("activity");
        $activityId = $tree->getActivity()->id;
        $data["status"] = $status;

        $this->json("patch", "/activities/$activityId/mark", $data);

        $path = Path::retrieve($tree->flatIds("activity"));

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "id" => $activityId,
            "mark" => $status
        ]);
        $this->assertInDatabase("marks", [
            "path_id" => $path->id,
            "user_id" => $this->user->id,
            "status" => $status
        ]);
    }

    /**
     * PATCH /activities/{id}/mark with {status: view}.
     *
     * @throws \Exception
     */
    public function testMarkView()
    {
        $this->mark("view");
    }

    /**
     * PATCH /activities/{id}/mark with {status: to_revise}.
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark("to_revise");
    }

    /**
     * PATCH /activities/{id}/mark with {status: completed}.
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark("completed");
    }

    /**
     * PATCH /activities/{id}/mark
     * Try to mark an activity which does not exist, assert HTTP 404 model_not_found.
     */
    public function testMarkNotExists()
    {
        $this->json("patch", "/activities/999999/mark", ["status" => "view"]);
        $this->assertHttp404ModelNotFound();
    }

    /**
     * PATCH /activities/{id}/mark with {status: completed} on existing mark with {status: view}.
     */
    public function testMarkOverride()
    {
        $mark = Mark::random(["status", "=", "view"]);
        Passport::actingAs($mark->user, [$mark->user->type]);

        $this->json("patch", "/activities/{$mark->path->activity_id}/mark", [
            "country_id" => $mark->path->country_id,
            "class_id" => $mark->path->class_id,
            "discipline_id" => $mark->path->discipline_id,
            "chapter_id" => $mark->path->chapter_id,
            "notion_id" => $mark->path->notion_id,
            "status" => "completed"
        ]);

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "mark" => "completed"
        ]);
        $this->assertInDatabase("marks", [
            "id" => $mark->id,
            "path_id" => $mark->path_id,
            "user_id" => $mark->user_id,
            "status" => "completed"
        ]);
    }
}

<?php

namespace Tests\API\Activities;

use App\Models\Mark;
use App\Models\Path;
use Laravel\Passport\Passport;
use Tests\Utils\Tree;

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
        $this->json("patch", "/activities/999999/mark", ["favorite" => true]);
        $this->assertHttp404ModelNotFound();
    }
}

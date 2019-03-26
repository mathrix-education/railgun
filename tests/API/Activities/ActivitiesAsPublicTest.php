<?php

namespace Tests\API\Activities;

use App\Models\Module;
use App\Models\Notion;
use App\Models\Path;
use App\Utils\Tree;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API\ScopeTestCase;

/**
 * Class ActivitiesAsPublicTest.
 * Test activities as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Activities
 */
class ActivitiesAsPublicTest extends ScopeTestCase
{
    /**
     * @covers \App\Controllers\ActivitiesController::index()
     * GET /activities -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /activities/{id}.
     *
     * @throws \Exception
     */
    public function testGetNotPremium()
    {
        $this->genericGetSuccess();
    }


    /**
     * GET /activities/by-notion/{id}.
     */
    public function testByNotion()
    {
        $notion = Notion::where("visible", "=", true)->firstOrFail();

        $this->json("get", "/activities/by-notion/{$notion->id}");

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        foreach ($responseData->data as $activity) {
            $this->assertInDatabase("activity_notion", [
                "activity_id" => $activity->id,
                "notion_id" => $notion->id,
            ]);
        }
    }


    /**
     * GET /activities/by-notion/{id}.
     */
    public function testByWtf()
    {
        $this->json("get", "/activities/by-wtf/4242666");

        $this->assertHttp400InvalidRequest();
    }


    /**
     * GET /activities/by-notion/{id}.
     */
    public function testIds()
    {
        $module = Module::inRandomOrder()
            ->where("visible", "=", true)
            ->firstOrFail();

        $this->json("get", "/activities/by-module/{$module->id}/ids");

        $activitiesIds = $this->getResponseData();

        $this->assertHttp200Success();
    }


    /**
     * POST /activities -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * @covers \App\Controllers\ActivitiesController::edit()
     * PATCH /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * @covers \App\Controllers\ActivitiesController::delete()
     * DELETE /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * PATCH /activities/{id}/mark with {to_revise: true}.
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark("to_revise");
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

        $this->assertHttp401Unauthorized();
    }


    /**
     * PATCH /activities/{id}/mark with {completed: true}.
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark("completed");
    }


    /**
     * GET /activities/by-route/{countryId}/{classId}/{disciplineId}/{chapterId}/{notionId}
     *
     * @throws \Exception
     */
    public function testByRoute()
    {
        $url = "/activities/by-route/{country_id}/{class_id}/{discipline_id}/{chapter_id}/{notion_id}";
        $tree = new Tree();
        $ids = $tree->flatIds("notion");

        // Set seo_index to true to the first activity
        $activity = $tree->getNotion()->activities[0];
        $path = Path::retrieve(
            array_merge(
                $ids,
                ["activity_id" => $activity->id, "module_id" => null]
            )
        );
        $path->seo_index = true;
        $path->save();

        foreach ($ids as $k => $v) {
            $url = str_replace("{" . $k . "}", $v, $url);
        }

        $this->json("get", $url);

        $this->assertResponseOk();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "[0].seo_index" => true,
            "[*].seo_index" => new IsType("boolean"),
        ]);
    }
}

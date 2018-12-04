<?php

namespace Tests\API\Activities;

use App\Models\Module;
use App\Models\Notion;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API\ScopeTestCase;
use Tests\Utils\Tree;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ActivitiesAsPublicTest extends ScopeTestCase
{
    /**
     * @covers \App\Controllers\ActivityController::index()
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
        $notion = Notion::whereVisible(true)->firstOrFail();

        $this->call("get", "/activities/by-notion/{$notion->id}");

        $activities = $this->getResponseData();

        $this->assertHttp200Success();
        foreach ($activities as $activity) {
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
        $this->call("get", "/activities/by-wtf/4242666");

        $this->assertHttp400InvalidRequest();
    }

    /**
     * GET /activities/by-notion/{id}.
     */
    public function testIds()
    {
        $module = Module::whereVisible(true)->firstOrFail();

        $this->call("get", "/activities/by-module/{$module->id}/ids");

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
     * @covers \App\Controllers\ActivityController::edit()
     * PATCH /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * @covers \App\Controllers\ActivityController::delete()
     * DELETE /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }

    /**
     * Generic mark test.
     *
     * @param bool|null $favorite
     * @param bool|null $to_revise
     * @param bool|null $completed
     *
     * @throws \Exception
     */
    public function mark($favorite, $to_revise, $completed)
    {
        $tree = new Tree();
        $data = $tree->flatIds("activity");
        $activityId = $tree->getActivity()->id;

        if ($favorite !== null) {
            $data["favorite"] = $favorite;
        }
        if ($to_revise !== null) {
            $data["to_revise"] = $to_revise;
        }
        if ($completed !== null) {
            $data["completed"] = $completed;
        }

        $this->call("patch", "/activities/$activityId/mark", $data);

        $this->assertHttp401Unauthorized();
    }

    /**
     * PATCH /activities/{id}/mark with {favorite: true}.
     *
     * @throws \Exception
     */
    public function testMarkFavorite()
    {
        $this->mark(null, null, false);
    }

    /**
     * PATCH /activities/{id}/mark with {to_revise: true}.
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }

    /**
     * PATCH /activities/{id}/mark with {completed: true}.
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }

    /**
     * PATCH /activities/{id}/mark
     * Assert meta updated_at field changes even if the meta content is the same.
     *
     * @throws \Exception
     */
    public function testMarkForceUpdatedAt()
    {
        $tree = new Tree();

        $this->call("patch", "/activities/{$tree->getActivity()->id}/mark", $tree->flatIds("activity"));
        $this->assertHttp401Unauthorized();
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

        foreach ($ids as $k => $v) {
            $url = str_replace("{" . $k . "}", $v, $url);
        }

        $this->json("get", $url);

        $this->assertResponseOk();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "[*].seo_index" => new IsType("boolean"),
        ]);
    }
}

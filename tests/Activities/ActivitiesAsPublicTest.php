<?php
namespace Tests\Activities;

use App\Models\Meta;
use Tests\ScopeTestCase;
use Tests\Tree;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Activities
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
     * GET /activities/{id}
     *
     * @throws \Exception
     */
    public function testGetNotPremium()
    {
        $this->genericGetSuccess();
    }


    /**
     * @covers \App\Controllers\ActivityController::add()
     * POST /activities -> HTTP 401 unauthorized
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
     * Generic mark test
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
        $data = $tree->flatIds("notion");
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
     * PATCH /activities/{id}/mark with {favorite: true}
     *
     * @throws \Exception
     */
    public function testMarkFavorite()
    {
        $this->mark(null, null, false);
    }


    /**
     * PATCH /activities/{id}/mark with {to_revise: true}
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }


    /**
     * PATCH /activities/{id}/mark with {completed: true}
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }


    /**
     * PATCH /activities/{id}/mark
     * Assert meta updated_at field changes even if the meta content is the same
     */
    public function testMarkForceUpdatedAt()
    {
        /** @var Meta $meta */
        $meta = Meta::random();
        $this->call("patch", "/activities/{$meta->activity_id}/mark", $meta->toArray());
        $this->assertHttp401Unauthorized();
    }
}

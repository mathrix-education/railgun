<?php

namespace Tests\API\Activities;

use App\Models\Meta;
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
     * GET /activities/favorites.
     *
     * @throws \Exception
     */
    public function testFavorites()
    {
        $this->get("/activities/favorites");

        $metas = $this->getResponseData();

        $this->assertHttp200Success();

        /** @var Meta $meta */
        foreach ($metas as $meta) {
            $this->assertEquals($this->user->id, $meta->user_id);
            $this->assertTrue($meta->favorite);
        }
    }

    /**
     * GET /activities/to-revise.
     *
     * @throws \Exception
     */
    public function testToRevise()
    {
        $this->get("/activities/to-revise");

        $metas = $this->getResponseData();

        $this->assertHttp200Success();

        /** @var Meta $meta */
        foreach ($metas as $meta) {
            $this->assertEquals($this->user->id, $meta->user_id);
            $this->assertTrue($meta->to_revise);
        }
    }

    /**
     * GET /activities/incomplete.
     *
     * @throws \Exception
     */
    public function testIncomplete()
    {
        $this->get("/activities/incomplete");

        $metas = $this->getResponseData();

        $this->assertHttp200Success();

        $disciplinesIncluded = [];

        /** @var Meta $meta */
        foreach ($metas as $meta) {
            $this->assertEquals($this->user->id, $meta->user_id);
            $this->assertNotTrue($meta->completed);
            $this->assertNotTrue(\in_array($meta->discipline_id, $disciplinesIncluded, true));
            $disciplinesIncluded[] = $meta->discipline_id;
        }
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

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);

        $this->assertHttp200Success();
        $this->assertInDatabase("metas", $expectedData);
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
     */
    public function testMarkForceUpdatedAt()
    {
        /** @var Meta $meta */
        $meta = Meta::random();
        Passport::actingAs($meta->user);
        $oldDate = $meta->updated_at;

        $this->call("patch", "/activities/{$meta->activity_id}/mark", $meta->toArray());

        $meta = Meta::find($meta->id);

        $this->assertTrue($oldDate < $meta->updated_at);
    }

    /**
     * PATCH /activities/{id}/mark
     * Try to mark an activity which does not exist, assert HTTP 404 model_not_found.
     */
    public function testMarkNotExists()
    {
        $this->call("patch", "/activities/999999/mark", ["favorite" => true]);
        $this->assertHttp404ModelNotFound();
    }
}

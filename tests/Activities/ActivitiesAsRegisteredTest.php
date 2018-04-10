<?php
namespace Tests\Activities;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Meta;
use App\Models\Notion;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsRegisteredTest extends ActivitiesAsPublicTest
{
    /**
     * Build objects for /activities/mark
     *
     * @return array
     *  int country_id
     *  int class_id
     *  int discipline_id
     *  int chapter_id
     *  int notion_id
     *  int activity_id
     */
    public function buildObjects()
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::inRandomOrder()->firstOrFail();
        /** @var Class_ $class */
        $class = $chapter->class_()->firstOrFail();
        /** @var Discipline $discipline */
        $discipline = $chapter->discipline()->firstOrFail();
        /** @var Country $country */
        $country = $class->country()->firstOrFail();
        /** @var Notion $notion */
        $notion = $chapter->notions()->inRandomOrder()->firstOrFail();
        /** @var Activity $activity */
        $activity = $notion->activities()->inRandomOrder()->firstOrFail();

        return [
            "country_id" => $country->id,
            "class_id" => $class->id,
            "discipline_id" => $discipline->id,
            "chapter_id" => $chapter->id,
            "notion_id" => $notion->id,
            "activity_id" => $activity->id
        ];
    }


    /**
     * Generic mark test
     *
     * @param bool|null $favorite
     * @param bool|null $to_revise
     * @param bool|null $completed
     */
    public function mark($favorite, $to_revise, $completed)
    {
        $data = $this->buildObjects();
        $activityId = $data["activity_id"];
        unset($data["activity_id"]);

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
     * GET /activities/favorites
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
     * GET /activities/to-revise
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
     * GET /activities/incomplete
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
            $this->assertNotTrue(in_array($meta->discipline_id, $disciplinesIncluded));
            $disciplinesIncluded[] = $meta->discipline_id;
        }
    }


    /**
     * PATCH /activities/{id}/mark with {favorite: true}
     */
    public function testMarkFavorite()
    {
        $this->mark(null, null, false);
    }


    /**
     * PATCH /activities/{id}/mark with {to_revise: true}
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }


    /**
     * PATCH /activities/{id}/mark with {completed: true}
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }
}

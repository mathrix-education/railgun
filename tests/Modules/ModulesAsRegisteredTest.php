<?php
namespace Tests\Modules;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class ModulesAsPublicTest
 * Test modules as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsRegisteredTest extends ModulesAsPublicTest
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
     *  int module_id
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
        /** @var Module $module */
        $module = $activity->modules()->inRandomOrder()->firstOrFail();

        return [
            "country_id" => $country->id,
            "class_id" => $class->id,
            "discipline_id" => $discipline->id,
            "chapter_id" => $chapter->id,
            "notion_id" => $notion->id,
            "activity_id" => $activity->id,
            "module_id" => $module->id
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
        $moduleId = $data["module_id"];
        unset($data["module_id"]);

        if ($favorite !== null) {
            $data["favorite"] = $favorite;
        }
        if ($to_revise !== null) {
            $data["to_revise"] = $to_revise;
        }
        if ($completed !== null) {
            $data["completed"] = $completed;
        }

        $this->call("patch", "/modules/$moduleId/mark", $data);

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);

        $this->assertHttp200Success();
        $this->assertInDatabase("metas", $expectedData);
    }

    /**
     * PATCH /modules/{id}/mark with {to_revise: true}
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }


    /**
     * PATCH /modules/{id}/mark with {completed: true}
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }
}

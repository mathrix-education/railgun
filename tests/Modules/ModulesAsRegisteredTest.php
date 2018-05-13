<?php
namespace Tests\Modules;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use Tests\Tree;

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
        $data = $tree->flatIds();
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
     * PATCH /modules/{id}/mark with {favorite: true}
     *
     * @throws \Exception
     */
    public function testMarkFavorite()
    {
        $this->mark(true, null, null);
    }


    /**
     * PATCH /modules/{id}/mark with {to_revise: true}
     *
     * @throws \Exception
     */
    public function testMarkToRevise()
    {
        $this->mark(null, true, null);
    }


    /**
     * PATCH /modules/{id}/mark with {completed: true}
     *
     * @throws \Exception
     */
    public function testMarkCompleted()
    {
        $this->mark(null, null, true);
    }
}

<?php

namespace Tests\API\Paths;

use App\Utils\Tree;

/**
 * Class PathsAsAdminTest
 * Test paths as admin.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.2.0
 */
class PathsAsAdminTest extends PathsAsModeratorTest
{
    /**
     * @throws \Exception
     */
    public function testMarkIndex()
    {
        $tree = new Tree();

        $this->json("post", "/paths/index", $tree->flatIds());

        $this->assertHttp200Success();
        $this->assertInDatabase("paths", [
            "country_id" => $tree->getCountry()->id,
            "class_id" => $tree->getClass()->id,
            "discipline_id" => $tree->getDiscipline()->id,
            "chapter_id" => $tree->getChapter()->id,
            "notion_id" => $tree->getNotion()->id,
            "activity_id" => $tree->getActivity()->id,
            "module_id" => null
        ]);
    }
}

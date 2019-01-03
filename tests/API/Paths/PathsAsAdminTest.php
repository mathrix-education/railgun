<?php
namespace Tests\API\Paths;

use App\Utils\Tree;

/**
 * Class PathsAsAdminTest.
 * Test paths as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @version   4.2.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Paths
 */
class PathsAsAdminTest extends PathsAsModeratorTest
{
    /**
     * @throws \Exception
     */
    public function testMarkIndex()
    {
        $tree = new Tree();

        $this->json("post", "/paths/index", array_merge(
            $tree->flatIds(),
            ["seo_index" => true]
        ));

        $this->assertHttp200Success();
        $this->assertInDatabase("paths", [
            "country_id" => $tree->getCountry()->id,
            "class_id" => $tree->getClass()->id,
            "discipline_id" => $tree->getDiscipline()->id,
            "chapter_id" => $tree->getChapter()->id,
            "notion_id" => $tree->getNotion()->id,
            "activity_id" => $tree->getActivity()->id,
            "module_id" => null,
            "seo_index" => true
        ]);
    }
}

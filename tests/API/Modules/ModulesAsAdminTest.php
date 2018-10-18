<?php

namespace Tests\API\Modules;

use Tests\Utils\Tree;

/**
 * Class ModulesAsAdminTest
 * Test modules as admin.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ModulesAsAdminTest extends ModulesAsModeratorTest
{
    public function testNoIndex()
    {
        $tree = new Tree();

        $this->json("post", "/modules/{$tree->getModule()->id}/no-index", $tree->flatIds());

        $this->assertInDatabase("metas", [
            "favorite" => false,
            "to_revise" => false,
            "completed" => false,
            "no_index" => true,
            "user_id" => null,
            "country_id" => $tree->getCountry()->id,
            "class_id" => $tree->getClass()->id,
            "discipline_id" => $tree->getDiscipline()->id,
            "chapter_id" => $tree->getChapter()->id,
            "notion_id" => $tree->getNotion()->id,
            "activity_id" => $tree->getActivity()->id,
            "module_id" => $tree->getModule()->id
        ]);
    }
}

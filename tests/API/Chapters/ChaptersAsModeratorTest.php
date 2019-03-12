<?php

namespace Tests\API\Chapters;

use App\Models\Notion;

/**
 * Class ChaptersAsModeratorTest.
 * Test chapters as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Chapters
 */
class ChaptersAsModeratorTest extends ChaptersAsRegisteredTest
{
    /**
     * GET /chapters.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /chapters.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /chapters/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * PATCH /chapters/{id} with linkable models.
     *
     * @throws \Exception
     */
    public function testEditWithLink()
    {
        $link = [
            "notions" => $this->faker->randomElements(
                Notion::inRandomOrder()->pluck("id")->toArray(),
                $this->faker->numberBetween(3, 5)
            ),
        ];

        $this->genericEditSuccess(["link" => $link]);

        foreach ($link["notions"] as $order => $id) {
            $this->assertInDatabase("chapter_notion", [
                "chapter_id" => $this->editModelId,
                "notion_id" => $id,
                "order" => $order,
            ]);
        }
    }


    /**
     * DELETE /chapters/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("chapter_notion", ["chapter_id" => $this->deleteModelId]);
    }
}

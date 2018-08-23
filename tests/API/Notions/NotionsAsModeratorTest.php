<?php

namespace Tests\API\Notions;

use App\Models\Activity;
use App\Models\Chapter;

/**
 * Class NotionsAsModeratorTest
 * Test notions as moderator.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class NotionsAsModeratorTest extends NotionsAsRegisteredTest
{
    /**
     * POST /notions.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /notions/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * PATCH /notions/{id}.
     *
     * @throws \Exception
     */
    public function testEditWithActivities()
    {
        $chapterIds = Chapter::inRandomOrder()->limit(3)->get(["id"])->pluck("id");
        $activityIds = Activity::inRandomOrder()->limit(3)->get(["id"])->pluck("id");

        $this->genericEditSuccess([
            "link" => [
                "chapters" => $chapterIds,
                "activities" => $activityIds,
            ],
        ]);

        foreach ($activityIds as $chapterId) {
            $this->assertInDatabase("activity_notion", [
                "activity_id" => $chapterId,
                "notion_id" => $this->editModelId,
            ]);
        }
        foreach ($chapterIds as $chapterId) {
            $this->assertInDatabase("chapter_notion", [
                "chapter_id" => $chapterId,
                "notion_id" => $this->editModelId,
            ]);
        }
    }

    /**
     * DELETE /notions/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_notion", ["notion_id" => $this->deleteModelId]);
    }
}

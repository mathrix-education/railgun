<?php

namespace Tests\API;

use App\Models\Chapter;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

/**
 * Class ChaptersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ChaptersTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * DELETE /chapters/{chapterId}
     */
    public function testDelete()
    {
        $chapter = Chapter::random();
        $pivot = Chapter::random(["id", "!=", $chapter->id]);

        $chapter->notions()->update(["chapter_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $chapter->id]]);
    }


    /**
     * GET /chapters/{chapterId}/recursive
     */
    public function testRecursive(): void
    {
        $chapter = Chapter::random();
        $uri = "/chapters/{$chapter->id}/recursive";

        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
    }


    /**
     * GET /chapters/by-grade-discipline/{gradeId}/{disciplineId}[/{scope}]
     */
    public function testByGradeIdDisciplineId(): void
    {
        $chapter = Chapter::random();
        $uri = "/chapters/by-grade-discipline/{$chapter->grade_id}/{$chapter->discipline_id}";

        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches([
            "[*].grade_id" => $chapter->grade_id,
            "[*].discipline_id" => $chapter->discipline_id
        ]);
    }


    /**
     * GET /chapters/slug/{chapterSlug}/by-grade-slug/{gradeSlug}
     */
    public function testGetBySlugGradeSlug(): void
    {
        $chapter = Chapter::random();
        $uri = "/chapters/slug/{$chapter->slug}/by-grade-slug/{$chapter->grade->slug}";

        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertJsonResponseMatches([
            "id" => $chapter->id,
            "slug" => $chapter->slug,
            "grade_id" => $chapter->grade_id,
        ]);
    }
}

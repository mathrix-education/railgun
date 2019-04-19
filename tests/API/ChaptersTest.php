<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Mark;
use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class ChaptersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
     * GET /chapters/recursive with cache counters
     */
    public function testChapterRecursiveWithCache()
    {
        /** @var Mark $mark */
        $mark = Mark::query()
            ->where("markable_type", "=", "activity")
            ->inRandomOrder()
            ->firstOrFail();

        $chapterId = $mark->markable->notion->chapter_id;

        $user = User::random(["type", "=", "registered"]);
        Passport::actingAs($user, ["registered"]);

        $uri = "/chapters/{$chapterId}/recursive";
        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
    }


    /**
     * GET /chapters/by-grade-discipline/{gradeId}/{disciplineId}[/{scope}]
     */
    public function testByGradeIdDisciplineId(): void
    {
        $chapter = Chapter::random(["visible", "=", true]);
        $uri = "/chapters/by-grade-discipline/{$chapter->grade_id}/{$chapter->discipline_id}";

        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
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
        $this->assertOpenAPIResponse();
    }
}

<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Mark;
use Illuminate\Support\Arr;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Tests\APIStatelessTestCase;

/**
 * Class ChaptersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChaptersStatelessTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardGetTest, StandardPatchTest;


    /**
     * POST /chapters
     * Standard post test.
     */
    public function testPost(): void
    {
        $this->assertRestPostSuccess(null, function ($data) {
            return Arr::except($data, ["order"]);
        });
    }


    /**
     * DELETE /chapters/{chapterId}
     */
    public function testDelete(): void
    {
        $chapter = Chapter::random();
        $pivot = Chapter::random(["id", "!=", $chapter->id]);

        $chapter->notions()->update(["chapter_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $chapter->id]]);
    }


    /**
     * GET /chapters/{chapterId}/notions/{page}/{perPage}
     */
    public function testNotions(): void
    {
        $this->assertRestRelationSuccess("notions");
    }


    /**
     * GET /chapters/{chapterId}/recursive
     */
    public function testRecursive(): void
    {
        $chapter = Chapter::random();
        $uri = "/chapters/{$chapter->id}/recursive";

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
        $uri = "/chapters/{$chapterId}/recursive";

        $this->json("get", $uri);

        $this->assertResponseOk();
    }
}

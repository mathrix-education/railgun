<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Mark;
use Illuminate\Support\Arr;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class ChaptersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChaptersTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:post",
        "std:get",
        "std:patch",
        "rel:get:notions"
    ];


    /**
     * DELETE /chapters/{chapterId}
     */
    public function testDelete(): void
    {
        $chapter = Chapter::random();
        $fallback = Chapter::random(["id", "!=", $chapter->id]);

        $chapter->notions()->update(["chapter_id" => $fallback->id]);

        $this->makeRESTJsonRequest("std:delete", null, null, [
            "conditions" => ["id", "=", $chapter->id]
        ]);
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
        // $this->assertOpenAPIResponse($this->response); // TODO: fix schema?
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

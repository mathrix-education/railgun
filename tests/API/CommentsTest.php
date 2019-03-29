<?php

namespace Tests\API;

use App\Models\Comment;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\Standard\StandardPostTest;

/**
 * Class CommentsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class CommentsTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest;


    /**
     * PATCH /comments/{commentId}
     */
    public function testPatch(): void
    {
        $comment = Comment::random();
        $newContent = "Edited?";
        DB::table("comments")
            ->where("id", "=", $comment->id)
            ->update(["updated_at" => Carbon::now()->__toString()]);
        Passport::actingAs($comment->user, ["registered"]);

        $this->json("patch", "/comments/{$comment->id}", [
            "content" => $newContent
        ]);

        $this->assertResponseOk();
        $this->assertInDatabase("comments", ["id" => $comment->id, "content" => $newContent]);
    }


    /**
     * GET /comments/by-module/{moduleId}
     */
    public function testByModule(): void
    {
        $this->assertRestBySuccess(Module::class);
    }


    /**
     * GET /comments/latest/{page}/{perPage}
     */
    public function testLatest(): void
    {
        $uri = "/comments/latest/1/10";

        $this->autoMockScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertIsPaginatedResponse(1, 10);
    }
}

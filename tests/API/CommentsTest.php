<?php

namespace Tests\API;

use App\Models\Comment;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

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

    public function testPost()
    {
        $this->assertRestPostSuccess(null, function($data) {
            $data["user_id"] = $this->passportUser->id;
            return Arr::except($data, []);
        });
    }


    /**
     * PATCH /comments/{commentId}
     */
    public function testPatch(): void
    {
        $comment = Comment::random();
        $newContent = "Edited?";
        DB::table("comments")
            ->where("id", "=", $comment->id)
            ->update(["updated_at" => Carbon::now()]);
        Passport::actingAs($comment->user, ["registered"]);

        $this->json("patch", "/comments/{$comment->id}", [
            "content" => $newContent
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertInDatabase("comments", ["id" => $comment->id, "content" => $newContent]);
    }


    /**
     * GET /comments/by-module/{moduleId}
     */
    public function testByModule(): void
    {
        /** @var Comment $commentChild */
        $commentChild = Comment::query()
            ->whereNotNull("parent_id")
            ->inRandomOrder()
            ->firstOrFail();

        $this->assertRestBySuccess(Module::class, ["conditions" => ["id", "=", $commentChild->module_id]]);
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
        $this->assertOpenAPIResponse();
    }
}

<?php

namespace Tests\API_old\Comments;

use App\Models\Comment;

/**
 * Class CommentsAsModeratorTest.
 * Test Comments as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Comments
 */
class CommentsAsModeratorTest extends CommentsAsRegisteredTest
{
    /**
     * GET /comments.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * PATCH /comments/{id} without changing the user_id.
     */
    public function testEdit()
    {
        /** @var Comment $comment */
        $commentId = Comment::inRandomOrder()->firstOrFail()->id;
        $data = factory(Comment::class)->raw();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->json("patch", "/comments/$commentId", $data);

        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $data);
    }


    /**
     * DELETE /comments/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericSoftDeleteSuccess();
    }


    /**
     * DELETE /comments/{id}.
     *
     * @throws \Exception
     */
    public function testDeleteWithChildren()
    {
        $this->deleteModelId = Comment::has("children")->firstOrFail()->id;
        $this->genericSoftDeleteSuccess();
    }


    /**
     * POST /comments/search.
     */
    public function testSearch()
    {
        $this->json("POST", "/comments/search", [
            "content" => "%lorem%",
        ]);

        $this->assertHttp200Success();
    }


    /**
     * GET /comments/latest.
     *
     * @throws \Exception
     */
    public function testLatest()
    {
        $this->json("get", "/comments/latest");

        $this->assertHttp200Success();
        $this->assertJsonLengthMax(100);
    }
}

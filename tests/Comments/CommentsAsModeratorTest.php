<?php

namespace Tests\Comments;

use App\Models\Comment;

/**
 * Class CommentsAsModeratorTest
 * Test Comments as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsModeratorTest extends CommentsAsRegisteredTest
{
    /**
     * GET /comments
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    
    /**
     * PATCH /comments/{id} without changing the user_id
     */
    public function testEdit()
    {
        /** @var Comment $comment */
        $commentId = Comment::inRandomOrder()->firstOrFail()->id;
        $data = factory(Comment::class)->raw();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->call("patch", "/comments/$commentId", $data);

        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $data);
    }


    /**
     * DELETE /comments/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericSoftDeleteSuccess();
    }
}
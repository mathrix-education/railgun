<?php
namespace Tests\Comments;

use App\Models\Comment;

/**
 * Class CommentsAsPublicTest
 * Test Comments as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsRegisteredTest extends CommentsAsPublicTest
{
    /**
     * POST /comments
     */
    public function testAdd()
    {
        $data = factory(Comment::class)->raw();
        unset($data["user_id"], $data["created_at"], $data["updated_at"]);

        $this->call("post", "/comments", $data);

        $expectedData = array_merge($data, ["user_id" => $this->user->id]);
        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $expectedData);
    }


    /**
     * POST /comments
     */
    public function testAddForStranger()
    {
        $stranger = $this->stranger();

        $data = factory(Comment::class)->raw();
        $data["user_id"] = $stranger->id;
        unset($data["created_at"], $data["updated_at"]);

        $this->call("post", "/comments", $data);

        $this->assertHttp401Unauthorized();
        $this->assertNotInDatabase("comments", $data);
    }
}

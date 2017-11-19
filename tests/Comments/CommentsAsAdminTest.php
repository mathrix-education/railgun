<?php
namespace Tests\Comments;

use App\Models\Comment;
use Tests\ScopeTestCase;

/**
 * Class CommentsAsAdminTest
 * Test Comments as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsAdminTest extends ScopeTestCase
{
    /**
     * GET /comments
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
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

        $this->assertHttp200Success();
        $this->assertInDatabase("comments", $data);
    }
}
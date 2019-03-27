<?php

namespace Tests\API_old\Comments;

use App\Models\Comment;
use App\Models\Module;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API_old\ScopeTestCase;

/**
 * Class CommentsAsPublicTest.
 * Test Comments as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Comments
 */
class CommentsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /comments.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /comments/by-module/{module_id}.
     */
    public function testByModule()
    {
        $module = Comment::inRandomOrder()
            ->whereNull("parent_id")
            ->firstOrFail()
            ->module;
        $this->genericBySuccess(Module::class, $module->id);

        $this->json("get", "/comments/by-module/{$module->id}");

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "data.[*].id" => new IsType("integer"),
            "data.[*].content" => new IsType("string"),
            "data.[*].user_id" => new IsType("integer"),
            "data.[*].path_id" => new IsType("integer"),
            "data.[*].created_at" => new IsType("string"),
            "data.[*].updated_at" => new IsType("string")
        ]);
    }


    /**
     * GET /comments/by-wtf/{wtf_id} -> HTTP 400 Invalid Request.
     *
     * @throws \Exception
     */
    public function testByNotExist()
    {
        $this->json("get", "/comments/by-wtf/" . random_int(1, 1000));

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /comments -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /comments/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /comments/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /comments/latest.
     *
     * @throws \Exception
     */
    public function testLatest()
    {
        $this->json("get", "/comments/latest");

        $this->assertHttp401Unauthorized();
    }
}

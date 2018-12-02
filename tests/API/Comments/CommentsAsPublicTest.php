<?php

namespace Tests\API\Comments;

use App\Models\Comment;
use App\Models\Module;
use PHPUnit\Framework\Constraint\IsType;
use Tests\API\ScopeTestCase;

/**
 * Class CommentsAsPublicTest
 * Test Comments as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
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

        $this->json("get", "/comments/by-module/{$module->id}");

        $this->assertHttp200Success();
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "[*].id" => new IsType("integer"),
            "[*].content" => new IsType("string"),
            "[*].user_id" => new IsType("integer"),
            "[*].path_id" => new IsType("integer"),
            "[*].created_at" => new IsType("string"),
            "[*].updated_at" => new IsType("string")
        ]);

    }

    /**
     * GET /comments/by-wtf/{wtf_id} -> HTTP 400 Invalid Request.
     *
     * @throws \Exception
     */
    public function testByNotExist()
    {
        $this->call("get", "/comments/by-wtf/" . random_int(1, 1000));

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
        $this->call("get", "/comments/latest");

        $this->assertHttp401Unauthorized();
    }
}

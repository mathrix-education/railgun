<?php
namespace Tests\Comments;


use App\Models\Module;
use App\Models\Post;
use Tests\ScopeTestCase;

/**
 * Class CommentsAsPublicTest
 * Test Comments as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /comments
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /comments/{commentable_type}/{commentable_id}
     */
    public function testBy()
    {
        $commentableType = $this->faker->randomElement(["Activity", "Post"]);
        /** @var Module|Post|string $typeClass */
        $typeClass = "App\\Models\\" . $commentableType;
        $commentableType = strtolower($commentableType);
        $commentableId = $typeClass::inRandomOrder()
            ->has("comments")
            ->firstOrFail()->id;

        $this->call("get", "/comments/by-$commentableType/$commentableId");

        // Assertions
        $this->assertHttp200Success();
    }


    public function testByNotExist()
    {
        $this->call("get", "/comments/by-lorem/" . random_int(1, 1000));

        // Assertions
        $this->assertHttp400InvalidRequest();
    }


    /**
     * POST /comments -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /comments/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /comments/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /comments/latest
     *
     * @throws \Exception
     */
    public function testLatest()
    {
        $this->call("get", "/comments/latest");

        $this->assertHttp401Unauthorized();
    }
}

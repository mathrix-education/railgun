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
 * @copyright Mathrix SA
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
    public function testType()
    {
        $commentableType = $this->faker->randomElement(["Module", "Post"]);
        /** @var Module|Post|string $typeClass */
        $typeClass = "App\\Models\\" . $commentableType;
        $commentableType = strtolower($commentableType);
        $commentableId = $typeClass::inRandomOrder()
            ->has("comments")
            ->firstOrFail()->id;

        $this->call("get", "/comments/$commentableType/$commentableId");

        // Assertions
        $this->assertHttp200Success();
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
}

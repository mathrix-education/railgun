<?php
namespace Tests\Comments;

use App\Models\BaseModel;
use Tests\ScopeTestCase;

/**
 * Class CommentsAsPublicTest
 * Test Comments as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsPublicTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }

    public function testType()
    {
        $commentableType = $this->faker->randomElement(['Module', 'Post', 'Course']);
        /** @var BaseModel $typeClass */
        $typeClass = 'App\\Models\\' . $commentableType;
        $commentableId = $typeClass::inRandomOrder()->has('comments')->firstOrFail()->id;

        $this->call('get', '/comments/' . strtolower($commentableType) . '/' . $commentableId);

        // Assertions
        $this->assertHttp200Success();
    }

    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
<?php
namespace Tests\Posts;

use Tests\ScopeTestCase;

/**
 * Class PostsAsPublicTest
 * Test Posts as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Posts
 */
class PostsAsPublicTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
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
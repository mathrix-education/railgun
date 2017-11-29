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
    /**
     * GET /notions
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /notions -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /notions/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /notions/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
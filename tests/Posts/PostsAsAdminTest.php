<?php
namespace Tests\Posts;

/**
 * Class PostsAsAdminTest
 * Test Posts as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Posts
 */
class PostsAsAdminTest extends PostsAsModeratorTest
{
    /**
     * POST /posts
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /posts/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /posts/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}

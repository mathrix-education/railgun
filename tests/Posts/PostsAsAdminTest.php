<?php
namespace Tests\Posts;

use Tests\ScopeTestCase;

/**
 * Class PostsAsAdminTest
 * Test Posts as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Posts
 */
class PostsAsAdminTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs("public");
    }


    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
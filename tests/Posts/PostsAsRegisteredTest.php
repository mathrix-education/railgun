<?php
namespace Tests\Posts;

use Tests\ScopeTestCase;

/**
 * Class PostsAsPublicTest
 * Test Posts as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Posts
 */
class PostsAsRegisteredTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('public');
    }


    public function testAdd()
    {
        $this->sameAs('public');
    }


    public function testEdit()
    {
        $this->sameAs('public');
    }


    public function testDelete()
    {
        $this->sameAs('public');
    }
}
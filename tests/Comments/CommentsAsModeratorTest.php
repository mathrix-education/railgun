<?php
namespace Tests\Comments;

use Tests\ScopeTestCase;

/**
 * Class CommentsAsModeratorTest
 * Test Comments as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsModeratorTest extends ScopeTestCase
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
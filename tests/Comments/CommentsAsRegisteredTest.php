<?php
namespace Tests\Comments;

use Tests\ScopeTestCase;

/**
 * Class CommentsAsPublicTest
 * Test Comments as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsRegisteredTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('public');
    }

    public function testAdd()
    {
        $this->genericAddSuccess();
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
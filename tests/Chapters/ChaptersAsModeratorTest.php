<?php
namespace Tests\Chapters;

use Tests\ScopeTestCase;

/**
 * Class ChaptersAsModeratorTest
 * Test chapters as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Chapters
 */
class ChaptersAsModeratorTest extends ScopeTestCase
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
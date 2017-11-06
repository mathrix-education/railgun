<?php
namespace Tests\Chapters;

use Tests\ScopeTestCase;

/**
 * Class ChaptersAsAdminTest
 * Test chapters as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Chapters
 */
class ChaptersAsAdminTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('moderator');
    }


    public function testAdd()
    {
        $this->sameAs('moderator');
    }


    public function testEdit()
    {
        $this->sameAs('moderator');
    }


    public function testDelete()
    {
        $this->sameAs('moderator');
    }
}
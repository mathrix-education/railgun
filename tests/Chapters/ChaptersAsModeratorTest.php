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
        $this->genericIndexSuccess();
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
        $this->assertNotInDatabase('notions', ['chapter_id' => $this->deleteModelId]);
    }
}
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
        $this->sameAs('public');
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
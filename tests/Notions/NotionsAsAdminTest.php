<?php
namespace Tests\Notions;

use Tests\ScopeTestCase;

/**
 * Class NotionsAsAdminTest
 * Test notions as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Notions
 */
class NotionsAsAdminTest extends ScopeTestCase
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
        $this->assertNotInDatabase('activity_notion', ['notion_id' => $this->deleteModelId]);
    }
}
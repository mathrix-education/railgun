<?php
namespace Tests\Activities;

use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsAdminTest
 * Test activities as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsAdminTest extends ScopeTestCase
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
        $this->assertNotInDatabase('activity_notion', ['activity_id' => $this->deleteModelId]);
        $this->assertNotInDatabase('activity_module', ['activity_id' => $this->deleteModelId]);
    }
}
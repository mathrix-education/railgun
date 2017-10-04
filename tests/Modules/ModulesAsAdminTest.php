<?php
namespace Tests\Modules;

use Tests\ScopeTestCase;

/**
 * Class ModulesAsAdminTest
 * Test modules as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsAdminTest extends ScopeTestCase
{
    protected $subFactory = 'random';

    public function testIndex()
    {
        $this->sameAs('public');
    }

    public function testAdd()
    {
        $this->genericAdd();
        $this->assertHttp200Success();
    }

    public function testEdit()
    {
        $this->genericEdit();
        $this->assertHttp200Success();
    }

    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase('activity_module', ['module_id' => $this->deleteModelId]);
    }
}
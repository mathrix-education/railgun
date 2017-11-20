<?php
namespace Tests\Modules;

/**
 * Class ModulesAsModeratorTest
 * Test modules as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsModeratorTest extends ModulesAsRegisteredTest
{
    /**
     * POST /modules
     */
    public function testAdd()
    {
        $this->genericAdd();
        $this->assertHttp200Success();
    }


    /**
     * PATCH /modules/{id}
     */
    public function testEdit()
    {
        $this->genericEdit();
        $this->assertHttp200Success();
    }


    /**
     * DELETE /modules/{id}
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_module", ["module_id" => $this->deleteModelId]);
    }
}
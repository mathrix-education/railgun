<?php
namespace Tests\Modules;

/**
 * Class ModulesAsModeratorTest
 * Test modules as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsModeratorTest extends ModulesAsRegisteredTest
{
    /**
     * POST /modules
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAdd();
        $this->assertHttp200Success();
    }


    /**
     * PATCH /modules/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEdit();
        $this->assertHttp200Success();
    }


    /**
     * DELETE /modules/{id}
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_module", ["module_id" => $this->deleteModelId]);
    }
}

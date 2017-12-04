<?php
namespace Tests\Disciplines;

/**
 * Class DisciplinesAsAdminTest
 * Test disciplines as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsAdminTest extends DisciplinesAsModeratorTest
{
    /**
     * POST /disciplines
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /disciplines/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /disciplines/{id} -> HTTP 501 not_implemented
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}

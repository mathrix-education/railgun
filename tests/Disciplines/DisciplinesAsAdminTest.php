<?php
namespace Tests\Disciplines;

/**
 * Class DisciplinesAsAdminTest
 * Test disciplines as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsAdminTest extends DisciplinesAsModeratorTest
{
    /**
     * POST /disciplines
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /disciplines/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /disciplines/{id} -> HTTP 501 not_implemented
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
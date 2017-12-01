<?php
namespace Tests\Classes;

/**
 * Class ClassesAsAdminTest
 * Test classes as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Classes
 */
class ClassesAsAdminTest extends ClassesAsModeratorTest
{
    /**
     * POST /classes
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /classes/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /classes/{id} -> HTTP 501 not_implemented
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
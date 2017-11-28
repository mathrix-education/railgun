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
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /classes/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /classes/{id} -> HTTP 501 not_implemented
     */
    public function testDelete()
    {
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
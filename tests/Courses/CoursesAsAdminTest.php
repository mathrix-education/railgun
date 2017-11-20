<?php
namespace Tests\Courses;

/**
 * Class CoursesAsAdminTest
 * Test Courses as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Courses
 */
class CoursesAsAdminTest extends CoursesAsModeratorTest
{
    /**
     * POST /courses
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /courses/{id}
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /courses/{id}
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
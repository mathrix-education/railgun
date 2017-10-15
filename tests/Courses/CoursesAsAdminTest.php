<?php
namespace Tests\Courses;

use Tests\ScopeTestCase;

/**
 * Class CoursesAsAdminTest
 * Test Courses as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Courses
 */
class CoursesAsAdminTest extends ScopeTestCase
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
    }
}
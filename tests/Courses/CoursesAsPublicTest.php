<?php
namespace Tests\Courses;

use Tests\ScopeTestCase;

/**
 * Class CoursesAsPublicTest
 * Test Courses as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Courses
 */
class CoursesAsPublicTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
<?php
namespace Tests\Courses;

use Tests\ScopeTestCase;

/**
 * Class CoursesAsModeratorTest
 * Test Courses as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Courses
 */
class CoursesAsModeratorTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('public');
    }


    public function testAdd()
    {
        $this->sameAs('public');
    }


    public function testEdit()
    {
        $this->sameAs('public');
    }


    public function testDelete()
    {
        $this->sameAs('public');
    }
}
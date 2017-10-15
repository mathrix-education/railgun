<?php
namespace Tests\Courses;

use Tests\ScopeTestCase;

/**
 * Class CoursesAsPremiumTest
 * Test Courses as premium user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Courses
 */
class CoursesAsPremiumTest extends ScopeTestCase
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
<?php
namespace Tests\Classes;

use Tests\ScopeTestCase;

/**
 * Class ClassesAsPremiumTest
 * Test classes as premium user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Classes
 */
class ClassesAsPremiumTest extends ScopeTestCase
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
<?php
namespace Tests\Disciplines;

use Tests\ScopeTestCase;

/**
 * Class DisciplinesAsAdminTest
 * Test disciplines as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsAdminTest extends ScopeTestCase
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
        $this->genericDelete();
        $this->assertHttp501NotImplemented();
    }
}
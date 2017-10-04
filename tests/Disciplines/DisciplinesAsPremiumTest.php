<?php
namespace Tests\Disciplines;

use Tests\ScopeTestCase;

/**
 * Class DisciplinesAsPremiumTest
 * Test disciplines as premium user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsPremiumTest extends ScopeTestCase
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
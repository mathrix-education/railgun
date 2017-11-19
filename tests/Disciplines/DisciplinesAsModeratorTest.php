<?php
namespace Tests\Disciplines;

use Tests\ScopeTestCase;

/**
 * Class DisciplinesAsModeratorTest
 * Test disciplines as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Disciplines
 */
class DisciplinesAsModeratorTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs("public");
    }


    public function testAdd()
    {
        $this->sameAs("public");
    }


    public function testEdit()
    {
        $this->sameAs("public");
    }


    public function testDelete()
    {
        $this->sameAs("public");
    }
}
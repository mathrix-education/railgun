<?php
namespace Tests\Chapters;

use Tests\ScopeTestCase;

/**
 * Class ChaptersAsPublicTest
 * Test chapters as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Chapters
 */
class ChaptersAsPublicTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
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
<?php
namespace Tests\Activities;

use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsPublicTest extends ScopeTestCase
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
<?php
namespace Tests\Classes;

use Tests\ScopeTestCase;

/**
 * Class ClassesAsPublicTest
 * Test classes as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Classes
 */
class ClassesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /classes/index
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /classes -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /classes/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /classes/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
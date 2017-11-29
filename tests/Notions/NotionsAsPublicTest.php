<?php
namespace Tests\Notions;

use Tests\ScopeTestCase;

/**
 * Class NotionsAsPublicTest
 * Test notions as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Notions
 */
class NotionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /notions
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /notions -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /notions/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /notions/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
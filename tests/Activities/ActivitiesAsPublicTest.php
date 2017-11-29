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
    /**
     * GET /activities -> HTTP 401 unauthorized
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * POST /activities -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /activities/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /activities/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
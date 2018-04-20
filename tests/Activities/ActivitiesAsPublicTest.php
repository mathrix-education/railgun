<?php
namespace Tests\Activities;

use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsPublicTest extends ScopeTestCase
{
    /**
     * @covers \App\Controllers\ActivityController::index()
     * GET /activities -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * @covers \App\Controllers\ActivityController::add()
     * POST /activities -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * @covers \App\Controllers\ActivityController::edit()
     * PATCH /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * @covers \App\Controllers\ActivityController::delete()
     * DELETE /activities/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

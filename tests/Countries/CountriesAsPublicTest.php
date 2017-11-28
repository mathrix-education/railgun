<?php
namespace Tests\Countries;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsPublicTest
 * Test countries as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Countries
 */
class CountriesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /countries
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /countries -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /countries/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /countries/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
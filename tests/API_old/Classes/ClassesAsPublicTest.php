<?php

namespace Tests\API_old\Classes;

use App\Models\Country;
use Tests\API_old\ScopeTestCase;

/**
 * Class ClassesAsPublicTest.
 * Test classes as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Classes
 */
class ClassesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /classes/index.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * GET /classes/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }


    /**
     * POST /classes -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /classes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /classes/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /classes/by-country/{country_id}.
     */
    public function testByCountry()
    {
        $this->genericBySuccess(Country::class);
    }
}

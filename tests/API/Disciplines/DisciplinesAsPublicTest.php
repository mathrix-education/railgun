<?php

namespace Tests\API\Disciplines;

use App\Models\Grade;
use Tests\API\ScopeTestCase;

/**
 * Class DisciplinesAsPublicTest.
 * Test disciplines as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Disciplines
 */
class DisciplinesAsPublicTest extends ScopeTestCase
{
    /**
     * GET /disciplines.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * GET /disciplines/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }


    /**
     * POST /disciplines -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /disciplines/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /disciplines/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }


    /**
     * GET /classes/by-class/{class_id}.
     */
    public function testByClass()
    {
        $this->genericBySuccess(Grade::class);
    }
}

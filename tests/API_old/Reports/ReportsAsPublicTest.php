<?php

namespace Tests\API_old\Reports;

use Tests\API_old\ScopeTestCase;

/**
 * Class ReportsAsPublicTest.
 * Test reports as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Reports
 */
class ReportsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /reports.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /reports/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetUnauthorized();
    }


    /**
     * POST /reports.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess([], function ($data) {
            unset($data["created_at"], $data["updated_at"]);

            return $data;
        });
    }


    /**
     * PATCH /reports/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /reports/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

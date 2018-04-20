<?php
namespace Tests\Reports;

use Tests\ScopeTestCase;

/**
 * Class ReportsAsPublicTest
 * Test reports as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Reports
 */
class ReportsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /reports
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /reports/{id}
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetUnauthorized();
    }


    /**
     * POST /reports
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /reports/{id}
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }
}

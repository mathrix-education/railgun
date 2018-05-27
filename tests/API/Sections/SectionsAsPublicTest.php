<?php
namespace Tests\API\Sections;

use Tests\API\ScopeTestCase;

/**
 * Class SectionAsPublicTest
 * Test section as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /sections
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /sections/{id}
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }



    /**
     * POST /sections -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /sections/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /sections/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

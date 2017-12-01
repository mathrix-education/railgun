<?php
namespace Tests\Sections;

use Tests\ScopeTestCase;

/**
 * Class SectionAsPublicTest
 * Test section as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Section
 */
class SectionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /section/index
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * POST /section -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /section/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /section/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
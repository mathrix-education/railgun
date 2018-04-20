<?php
namespace Tests\Lessons;


use Tests\ScopeTestCase;

/**
 * Class LessonsAsPublicTest
 * Test lessons as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.1
 * @package Tests\Lessons
 */
class LessonsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /lessons
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexUnauthorized();
    }


    /**
     * GET /lessons
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetUnauthorized();
    }


    /**
     * POST /lessons -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /lessons/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /lessons/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

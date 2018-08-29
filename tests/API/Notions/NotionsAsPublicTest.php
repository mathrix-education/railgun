<?php

namespace Tests\API\Notions;

use Tests\API\ScopeTestCase;

/**
 * Class NotionsAsPublicTest
 * Test notions as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class NotionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /notions.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * GET /notions/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }

    /**
     * POST /notions -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * PATCH /notions/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * DELETE /notions/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

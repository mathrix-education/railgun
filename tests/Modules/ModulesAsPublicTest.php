<?php
namespace Tests\Modules;

use Tests\ScopeTestCase;

/**
 * Class ModulesAsPublicTest
 * Test modules as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Modules
 */
class ModulesAsPublicTest extends ScopeTestCase
{
    /**
     * @var string The subFactory to use
     */
    protected $subFactory = "random";


    /**
     * GET /modules
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * POST /modules -> HTTP 401 unauthorized
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /modules/{id} -> HTTP 401 unauthorized
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /modules/{id} -> HTTP 401 unauthorized
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}
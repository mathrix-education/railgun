<?php
namespace Tests\Modules;

use App\Models\Module;
use Tests\ScopeTestCase;

/**
 * Class ModulesAsPublicTest
 * Test modules as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
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
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    /**
     * GET /modules/{id}
     */
    public function testGet()
    {
        /** @var Module $module */
        $module = Module::random();
        $this->get("/modules/{$module->id}");

        $this->assertHttp200Success();
    }


    /**
     * POST /modules -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }


    /**
     * PATCH /modules/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }


    /**
     * DELETE /modules/{id} -> HTTP 401 unauthorized
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

<?php

namespace Tests\API\Modules;

use App\Models\Activity;
use App\Models\Module;
use Tests\API\ScopeTestCase;

/**
 * Class ModulesAsPublicTest
 * Test modules as public (non-logged user).
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 1.0.0
 */
class ModulesAsPublicTest extends ScopeTestCase
{
    /**
     * @var string The subFactory to use
     */
    protected $subFactory = "random";

    /**
     * GET /modules.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * GET /modules/by-activity/{activity_id}.
     */
    public function testByActivity()
    {
        $this->genericBySuccess(Activity::class);

        $data = $this->getResponseData();

        foreach ($data as $order => $module) {
            $this->assertEquals($order, $module->pivot->order);
        }
    }

    /**
     * GET /modules/{id}.
     */
    public function testGet()
    {
        /** @var Module $module */
        $module = Module::wherePremium(false)->inRandomOrder()->firstOrFail();
        $this->get("/modules/{$module->id}");

        $this->assertHttp200Success();
    }

    /**
     * GET /modules/{id} with a premium module as non premium user.
     *
     * @throws \Exception
     */
    public function testGetPremiumAsNonPremium()
    {
        /** @var Module $module */
        $module = Module::wherePremium(true)->inRandomOrder()->firstOrFail();
        $this->get("/modules/{$module->id}");

        $response = $this->getResponseData();
        $responseKeys = array_keys((array) $response);
        $expectedKeys = ["id", "premium"];
        $this->assertTrue($response->premium);
        $this->assertEquals($expectedKeys, $responseKeys);
    }

    /**
     * POST /modules -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * PATCH /modules/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditUnauthorized();
    }

    /**
     * DELETE /modules/{id} -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteUnauthorized();
    }
}

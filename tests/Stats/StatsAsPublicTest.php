<?php
namespace Tests\Stats;

use Tests\ScopeTestCase;

/**
 * Class StatsAsPublicTest
 * Test stats as public (non-logged user)
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Section
 */
class StatsAsPublicTest extends ScopeTestCase
{
    /**
     * POST /stats/activities -> HTTP 401 unauthorized
     */
    public function testActivities()
    {
        $this->call("post", "/stats/activities");
        $this->assertHttp401Unauthorized();
    }
}

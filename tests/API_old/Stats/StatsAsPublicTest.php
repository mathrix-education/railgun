<?php

namespace Tests\API_old\Stats;

use Tests\API_old\ScopeTestCase;

/**
 * Class StatsAsPublicTest.
 * Test stats as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Stats
 */
class StatsAsPublicTest extends ScopeTestCase
{
    /**
     * POST /stats/activities -> HTTP 401 unauthorized.
     */
    public function testActivities()
    {
        $this->json("post", "/stats/activities");
        $this->assertHttp401Unauthorized();
    }
}

<?php

namespace Tests\API\Stats;

/**
 * Class StatsAsModeratorTest.
 * Test stats as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Stats
 */
class StatsAsModeratorTest extends StatsAsRegisteredTest
{
    /**
     * POST /stats/activities as stranger -> HTTP 401 unauthorized.
     *
     * @throws \Exception
     */
    public function testActivitiesForStranger()
    {
        $this->testActivities();
    }
}

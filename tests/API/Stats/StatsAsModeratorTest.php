<?php

namespace Tests\API\Stats;

/**
 * Class StatsAsModeratorTest
 * Test stats as moderator.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 1.0.0
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

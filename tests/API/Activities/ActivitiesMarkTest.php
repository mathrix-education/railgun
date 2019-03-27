<?php

namespace Tests\API\Activities;

use App\Models\Activity;
use Tests\API\APITestCase;

/**
 * Class ActivitiesMarkTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ActivitiesMarkTest extends APITestCase
{
    /**
     * PATCH /activities/{activityId}/mark
     */
    public function testMark()
    {
        $this->mockScope("registered");
        $activity = Activity::random();

        $this->json("patch", "/activities/{$activity->id}/mark");
    }
}

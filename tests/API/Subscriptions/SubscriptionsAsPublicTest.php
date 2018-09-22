<?php

namespace Tests\API\Subscriptions;

use App\Controllers\SubscriptionController;
use Tests\API\ScopeTestCase;

/**
 * Class SubscriptionsAsPublicTest
 * Test subscriptions as public (non-logged user).
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.1.0
 */
class SubscriptionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /subscriptions/estimate/{planId}.
     */
    public function testEstimate()
    {
        $planId = $this->faker->randomElement(["v4_annually", "v4_biannually"]);

        $response = $this->json("get", "/subscriptions/estimate/{$planId}");

        $response->assertHttp200Success()
            ->assertJsonKeys(["planId", "trialPrice", "trialEndDate"]);
    }
}

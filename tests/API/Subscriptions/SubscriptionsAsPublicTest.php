<?php

namespace Tests\API\Subscriptions;

use App\Controllers\SubscriptionController;
use Tests\API\ScopeTestCase;

/**
 * Class SubscriptionsAsPublicTest
 * Test subscriptions as public (non-logged user).
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 4.1.0
 */
class SubscriptionsAsPublicTest extends ScopeTestCase
{
    /**
     * GET /subscriptions/estimate/{planId}.
     */
    public function testEstimate()
    {
        $planId = $this->faker->randomElement(array_keys(SubscriptionController::PRICE_PER_MONTH));

        $response = $this->json("get", "/subscriptions/estimate/{$planId}");

        $response->assertHttp200Success()
            ->assertJsonKeys(["price", "end_date"]);
    }
}

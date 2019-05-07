<?php

namespace Tests\Integration\Braintree;

use Illuminate\Support\Facades\Notification;
use Mathrix\Lumen\JWT\Auth\JWT;

/**
 * Class SubscriptionLockedTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-dev
 */
class SubscriptionLockedTest extends BraintreeTestCase
{
    /**
     * POST /subscriptions assert that user cannot subscribe while being locked.
     */
    public function testLocked()
    {
        $user = $this->getPristineCustomer();
        $user->braintreeLock();
        JWT::actingAs($user);
        Notification::fake();

        $this->json("post", "/subscriptions", [
            "planId" => "v4_biannually",
            "paymentMethodNonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        // Response assertions
        $this->assertResponseStatus(429);
    }
}

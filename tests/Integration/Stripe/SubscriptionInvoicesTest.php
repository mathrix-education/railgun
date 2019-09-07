<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;

/**
 * Class SubscriptionInvoicesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionInvoicesTest extends APIStatelessTestCase
{
    /**
     * GET /subscriptions/{subscriptionId}/invoices
     */
    public function testInvoices()
    {
        // Create a subscription
        $this->requester = factory(User::class)->create();
        $this->requester->createStripeCustomer("tok_fr");
        $subscription = Subscription::create([
            "customer" => $this->requester->stripe_id,
            "default_source" => $this->requester->asStripeCustomer()->default_source,
            "items" => [
                ["plan" => SubscriptionUtils::MONTHLY_PLAN]
            ],
            "metadata" => [
                "user_id" => $this->requester->id,
            ]
        ]);

        JWT::actingAs($this->requester);

        $this->json("get", "/subscriptions/{$subscription->id}/invoices");

        $this->assertResponseStatus(200);
        $this->assertOpenAPIResponse($this->response);
    }
}

<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\Subscription;
use Braintree\Test\Nonces;
use Illuminate\Support\Facades\Notification;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\JWT\Auth\JWT;
use stdClass;

/**
 * Class SubscriptionLifecycleTest.
 * Test the full subscription lifecycle.
 * Note: We do not use BraintreeTestCase here because we need to persist data.
 *
 * 1. Pristine customer subscribes to v4_monthly using a visa card
 * 2. Admin gets the subscription
 * 3. User gets his subscriptions
 * 4. User updates the subscription payment method to a PayPal account
 * 5. Admin cancels the subscription at the end of the current billing cycle, user keeps the premium privileges
 * 6. Admin cancels the subscription now, user keeps the premium privileges
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class SubscriptionLifecycleTest extends BraintreeTestCase
{
    /**
     * POST /subscription
     * Create a new v4_monthly subscription.
     *
     * @return array
     * @throws Http400BadRequestException
     * @throws NotFound
     */
    public function testSubscriptionCreation()
    {
        Notification::fake();
        /** @var User $user */
        $user = JWT::withScopes("logged");

        $this->json("post", "/subscriptions", [
            "planId" => "v4_monthly",
            "paymentMethodNonce" => Nonces::$transactableVisa
        ]);

        $user->refresh();
        $customer = Customer::find($user->braintree_id);
        $subscription = $customer->creditCards[0]->subscriptions[0];

        $this->assertResponseOk();

        $subscriptionIdFromResponse = $this->getJsonResponseValue("id");

        $this->assertNotEmpty($customer->creditCards);
        $this->assertEquals($customer->id, $user->braintree_id);
        $this->assertTrue($user->premium);
        $this->assertNotNull($user->braintree_id);

        $this->assertEquals($subscription->id, $subscriptionIdFromResponse);
        $this->assertFalse($subscription->trialPeriod);
        $this->assertEquals($subscription->status, "Active");
        $this->assertEquals($subscription->planId, "v4_monthly");
        $this->assertTrue($user->ownsBraintreeSubscriptionId($subscription->id));

        // Check that a mail notification was sent
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $user, "mail");


        return [$user, $subscription];
    }


    /**
     * @depends testSubscriptionCreation
     * GET /subscription/{subscriptionId}
     * Get the previously created subscription.
     *
     * @param array $payload
     *
     * @return array
     */
    public function testSubscriptionFind(array $payload)
    {

        /**
         * @var User $user
         * @var Subscription $subscription
         */
        [$user, $subscription] = $payload;

        JWT::withScopes("subscriptions:read");
        $this->json("get", "/subscriptions/{$subscription->id}");

        $this->assertResponseOk();

        $subscriptionIdFromResponse = $this->getJsonResponseValue("id");
        $this->assertEquals($subscription->id, $subscriptionIdFromResponse);

        return [$user, $subscription];
    }


    /**
     * @depends testSubscriptionCreation
     * GET /subscription/{subscriptionId}
     * Get the previously created subscription.
     *
     * @param array $payload
     *
     * @return array
     */
    public function testSubscriptionByUser(array $payload)
    {
        /**
         * @var User $user
         * @var Subscription $subscription
         */
        [$user, $subscription] = $payload;
        JWT::actingAs($user);

        $this->json("get", "/subscriptions/by-user/{$user->id}");

        $this->assertResponseOk();
        $responseData = json_decode($this->response->getContent());
        $this->assertArrayHasKey(0, $responseData);
        $this->assertEquals($subscription->id, $responseData[0]->id);

        return [$user, $subscription];
    }


    /**
     * @depends testSubscriptionByUser
     * POST /subscriptions/{subscriptionId}
     * Cancel the subscription at the end of the current period.
     *
     * @param array $payload
     *
     * @return array
     */
    public function testSubscriptionChangePaymentMethod(array $payload)
    {
        /**
         * @var User $user
         * @var Subscription $subscription
         */
        [$user, $subscription] = $payload;
        JWT::actingAs($user);

        $this->json("patch", "/subscriptions/{$subscription->id}", [
            "payment_method_nonce" => Nonces::$paypalBillingAgreement,
            "make_default" => true
        ]);

        $this->assertResponseOk();
        $this->assertNotEquals($subscription->paymentMethodToken, $this->getJsonResponseValue("paymentMethodToken"));

        $subscription = Subscription::find($subscription->id); // Refresh subscription

        return [$user, $subscription];
    }


    /**
     * @depends testSubscriptionChangePaymentMethod
     * POST /subscriptions/{subscriptionId}/cancel
     * Cancel the subscription at the end of the current period.
     *
     * @param array $payload
     *
     * @return array
     */
    public function testSubscriptionCancellationAtTheEndOfCurrentPeriod(array $payload)
    {
        /**
         * @var User $user
         * @var Subscription $subscription
         */
        [$user, $subscription] = $payload;

        JWT::withScopes("subscriptions:write");
        $this->json("post", "/subscriptions/{$subscription->id}/cancel");

        /** @var stdClass|Subscription $subscriptionData */
        $subscriptionData = $this->getJsonResponseContent();

        $this->assertResponseOk();
        $this->assertTrue($user->premium); // Customer should keep premium

        $this->assertEquals(Subscription::ACTIVE, $subscription->status);
        $this->assertFalse($subscriptionData->neverExpires);
        $this->assertEquals($subscriptionData->currentBillingCycle, $subscriptionData->numberOfBillingCycles);

        return [$user, $subscription];
    }


    /**
     * @depends testSubscriptionCancellationAtTheEndOfCurrentPeriod
     * POST /subscriptions/{subscriptionId}/cancel
     * Cancel the subscription now.
     *
     * @param array $payload
     *
     * @return array
     */
    public function testSubscriptionCancelNow(array $payload)
    {
        /**
         * @var User $user
         * @var Subscription $subscription
         */
        [$user, $subscription] = $payload;
        JWT::withScopes("subscriptions:write");

        $this->json("post", "/subscriptions/{$subscription->id}/cancel", [
            "now" => true
        ]);

        $user->refresh();

        /** @var stdClass|Subscription $subscriptionData */
        $this->assertResponseOk();
        $this->assertEquals(Subscription::CANCELED, $this->getJsonResponseValue("status"));
        $this->assertFalse($user->premium); // Customer should loose premium

        return [$user, $subscription];
    }
}

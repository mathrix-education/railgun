<?php
namespace Tests\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Class SubscriptionsTest
 *
 * Scenario
 * - Non-Braintree Customer subscribes to v4_monthly plan
 * - Support cancels the subscription
 * - Support display the linked transactions
 * - Support refunds the last transaction
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class V4MonthlyIntegrationTest extends BraintreeTestCase
{
    /**
     * PATCH /users/{id}/card
     *
     * @return User
     */
    public function testCreateCustomer()
    {
        $customer = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();

        Passport::actingAs($customer, ["registered"]);

        $this->json("patch", "/users/{$customer->id}/payment-method", [
            "payment_method_nonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $customer->id,
            "braintree_id" => null
        ]);

        return $customer;
    }


    /**
     * @depends testCreateCustomer
     * POST /braintree/subscriptions
     *
     * @param User $customer
     *
     * @return string
     * @throws Http400InvalidRequestException
     */
    public function testSubscribeMonthly(User $customer)
    {
        Passport::actingAs($customer, ["registered"]);

        $this->json("post", "/subscriptions", [
            "plan" => "v4_monthly"
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertTrue($customer->hasBraintreeSubscription("v4_monthly"));
        $this->assertTrue($responseData->premium);

        return $customer->getBraintreeSubscriptions()[0]->id;
    }


    /**
     * @depends testSubscribeMonthly
     *
     * POST /subscriptions/{id}/cancel
     *
     * @param string $subscriptionId
     *
     * @return string
     */
    public function testCancel(string $subscriptionId)
    {
        $user = User::where("type", "=", "support")->firstOrFail();
        Passport::actingAs($user, ["support"]);

        $this->json("post", "/subscriptions/{$subscriptionId}/cancel");

        $this->assertHttp200Success();

        return $subscriptionId;
    }


    /**
     * @depends testCancel
     *
     * GET /subscriptions/{id}/transactions
     *
     * @param string $subscriptionId
     *
     * @return string
     */
    public function testGetSubscriptionTransactions(string $subscriptionId)
    {
        $user = User::where("type", "=", "admin")->firstOrFail();
        Passport::actingAs($user, ["admin"]);

        $this->json("get", "/subscriptions/{$subscriptionId}/transactions");

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();

        return $responseData[0]->id;
    }


    /**
     * @depends testGetSubscriptionTransactions
     *
     * @param string $transactionId
     *
     * POST /transactions/{id}/refund
     *
     * @return string
     */
    public function testRefundLastTransaction(string $transactionId)
    {
        $user = User::where("type", "=", "support")->firstOrFail();
        Passport::actingAs($user, ["support"]);

        $this->json("post", "/transactions/{$transactionId}/refund");

        $this->assertHttp200Success();

        return $transactionId;
    }


    /**
     * @depends testRefundLastTransaction
     *
     * @param string $transactionId
     *
     * Get /transactions/{id}
     */
    public function testGetLastTransaction(string $transactionId)
    {
        $user = User::where("type", "=", "support")->firstOrFail();
        Passport::actingAs($user, ["support"]);

        $this->json("get", "/transactions/{$transactionId}");

        $this->assertHttp200Success();
    }
}

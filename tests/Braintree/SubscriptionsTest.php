<?php
namespace Tests\Braintree;

use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Class SubscriptionsTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class SubscriptionsTest extends BraintreeTestCase
{
    /**
     * POST /braintree/subscriptions
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeMonthlyAsNonCustomer()
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $this->json("post", "/braintree/subscriptions", [
            "plan" => "v4_monthly",
            "payment_method_nonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $user->id,
            "braintree_id" => null
        ]);
        $this->assertTrue($user->hasBraintreeSubscription("v4_monthly"));
        $this->assertTrue($responseData->premium);
    }


    /**
     * POST /braintree/subscriptions
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeAnnuallyAsNonCustomer()
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $this->json("post", "/braintree/subscriptions", [
            "plan" => "v4_annually",
            "payment_method_nonce" => "fake-valid-visa-nonce"
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $user->id,
            "braintree_id" => null
        ]);
        $this->assertTrue($user->hasBraintreeSubscription("v4_annually"));
        $this->assertTrue($responseData->premium);
    }
}

<?php
namespace Tests\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Class StripeMigrationIntegrationTest
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   Tests\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class StripeMigrationIntegrationTest extends BraintreeTestCase
{
    /**
     * Test migration from Stripe to Braintree
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function testStripeMigration()
    {
        // Create Stripe customer
        $user = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->firstOrFail();

        /** @var \Stripe\Customer $stripeCustomer */
        $stripeCustomer = \Stripe\Customer::create([
            "email" => $user->email,
            "source" => "tok_visa"
        ]);

        $user->stripe_id = $stripeCustomer->id;
        $user->save();

        // Create Stripe subscription
        /** @var \Stripe\Subscription $subscription */
        $subscription = \Stripe\Subscription::create([
            "customer" => $user->stripe_id,
            "items" => [
                [
                    "plan" => "v3_monthly"
                ]
            ]
        ]);
        Passport::actingAs($user, ["registered"]);

        // Do request
        $this->json("patch", "/users/{$user->id}/payment-method", [
            "paymentMethodNonce" => "fake-valid-visa-nonce"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("users", ["id" => $user->id, "stripe_id" => null]);

        // Assert Stripe subscriptions were canceled
        $subscription = \Stripe\Subscription::retrieve($subscription->id);
        $this->assertEquals($subscription->status, "canceled");

        // Assert Braintree subscriptions were created
        $braintreeSubscriptions = $user->getBraintreeSubscriptions();
        $this->assertNotEmpty($braintreeSubscriptions);
        /** @var \Braintree\Subscription $newBraintreeSubscription */
        $newBraintreeSubscription = $braintreeSubscriptions->first();
        $this->assertEquals($newBraintreeSubscription->planId, "v4_monthly");
    }
}

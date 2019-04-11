<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Braintree\Subscription;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;
use Stripe\Customer;

/**
 * Class StripeMigrationIntegrationTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class StripeMigrationIntegrationTest extends BraintreeTestCase
{
    /** @var \Stripe\Subscription $stripeSubscription */
    private $stripeSubscription;


    /**
     * Create and initialize a valid Stripe customer.
     * @return User
     * @throws ValidationException
     */
    private function createStripeCustomer()
    {
        $user = $this->getPristineCustomer();

        /** @var Customer $stripeCustomer */
        $stripeCustomer = Customer::create([
            "email" => $user->email,
            "source" => "tok_visa"
        ]);

        $user->stripe_id = $stripeCustomer->id;
        $user->save();

        // Create Stripe subscription
        $this->stripeSubscription = \Stripe\Subscription::create([
            "customer" => $user->stripe_id,
            "items" => [
                [
                    "plan" => "v3_monthly"
                ]
            ]
        ]);

        return $user;
    }


    /**
     * Test migration from Stripe to Braintree
     *
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    public function testStripeMigration()
    {
        // Create Stripe customer
        $user = $this->createStripeCustomer();
        Passport::actingAs($user, ["registered"]);

        // Do request
        $this->json("patch", "/users/{$user->id}/payment-method", [
            "paymentMethodNonce" => "fake-valid-visa-nonce"
        ]);

        $this->assertResponseOk();
        $this->assertInDatabase("users", ["id" => $user->id, "stripe_id" => null]);

        // Assert Stripe subscriptions were canceled
        /** @var \Stripe\Subscription $subscription */
        $subscription = \Stripe\Subscription::retrieve($this->stripeSubscription->id);
        $this->assertEquals($subscription->status, "canceled");

        // Assert Braintree subscriptions were created
        $braintreeSubscriptions = $user->getBraintreeSubscriptions();
        $this->assertNotEmpty($braintreeSubscriptions);

        /** @var Subscription $newBraintreeSubscription */
        $newBraintreeSubscription = $braintreeSubscriptions->first();
        $this->assertEquals($newBraintreeSubscription->planId, "v4_monthly");
    }
}

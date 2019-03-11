<?php
namespace Tests\Braintree;

use App\Models\User;
use Braintree\Customer;
use Braintree\Subscription;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;

/**
 * Class SubscriptionCreationTest.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since     4.3.0
 */
class SubscriptionCreationTest extends BraintreeTestCase
{
    /**
     * @throws \Braintree\Exception\NotFound
     */
    public function testSubscription(): void
    {
        Mail::fake();

        /** @var User $user */
        $user = User::query()
            ->whereNull("stripe_id")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($user, ["registered"]);

        $this->json("post", "/subscriptions", [
            "planId" => "v4_biannually",
            "paymentMethodNonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        // Response assertions
        $this->assertResponseOk();

        // Subscription assertions
        $subId = $this->getResponseData()->id;
        $subscription = Subscription::find($subId);
        $this->assertEquals($subscription->id, $subId);
        $this->assertTrue($subscription->trialPeriod);
        $this->assertEquals($subscription->status, "Active");
        $this->assertEquals($subscription->planId, "v4_biannually");

        // Customer assertions
        $user = $user->refresh();
        $customer = Customer::find($user->braintree_id);
        $this->assertEquals($customer->id, $user->braintree_id);
        $this->assertNotEmpty($customer->paypalAccounts);
        $this->assertTrue($user->premium);

        // PayPal Account assertions
        $paypalSubscription = $customer->paypalAccounts[0]->subscriptions[0];
        $this->assertTrue($paypalSubscription->trialPeriod);
        $this->assertEquals($paypalSubscription->status, "Active");
        $this->assertEquals($paypalSubscription->planId, "v4_biannually");
    }
}

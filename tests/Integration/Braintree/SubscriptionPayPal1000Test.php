<?php

namespace Tests\Integration\Braintree;

use App\Notifications\TransactionSuccessNotification;
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\Subscription;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Tests\Utils\NotificationsAssertionsTrait;

/**
 * Class SubscriptionPayPal1000Test.
 *
 * Scenario:
 * ========
 *
 * testPayWithPayPal1000
 * ------------------
 * A user who is not already a Braintree customer will attempt to subscribe to the v4_biannually plan with his PayPal
 * account and everything will work like a charm.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SubscriptionPayPal1000Test extends BraintreeTestCase
{
    use NotificationsAssertionsTrait;


    /**
     * @throws NotFound
     * @throws Http400BadRequestException
     */
    public function testPayWithPayPal1000(): void
    {
        $user = $this->getPristineCustomer();
        Passport::actingAs($user, ["registered"]);
        Notification::fake();

        $this->json("post", "/subscriptions", [
            "planId" => "v4_biannually",
            "paymentMethodNonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        // Response assertions
        $this->assertResponseOk();

        // Subscription assertions
        $user = $user->refresh();
        $subscription = Subscription::find($this->getJsonResponseValue("id"));

        $this->assertTrue($user->ownsBraintreeSubscriptionId($subscription->id));

        // Check that the customer is premium
        $customer = Customer::find($user->braintree_id);
        $this->assertEquals($customer->id, $user->braintree_id);
        $this->assertNotEmpty($customer->paypalAccounts);
        $this->assertTrue($user->premium);
        $this->assertNotNull($user->braintree_id);

        // Check that the PayPal account got the newly created subscription
        $payPalSubscription = $customer->paypalAccounts[0]->subscriptions[0];
        $this->assertTrue($payPalSubscription->trialPeriod);
        $this->assertEquals($payPalSubscription->status, "Active");
        $this->assertEquals($payPalSubscription->planId, "v4_biannually");

        // Check that a mail notification was sent
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $user, "mail");
    }
}

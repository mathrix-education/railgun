<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use App\Services\Braintree\Models\Subscription;
use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\CreditCard;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Exception;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use PHPUnit\Framework\Constraint\StringStartsWith;

/**
 * Class Visa2001ThenPayPalScenarioTest.
 *
 * Scenario:
 * ========
 *
 * testPayWithVisa2001:
 * --------------------
 * A user who is not already a Braintree customer will attempt to subscribe to the v4_biannually plan firstly use a
 * VISA credit card, but the process will fail during the initial transaction with code 2001 (Insufficient Funds).
 *
 * testPayWithPayPal1000:
 * ------------------
 * The previous user will then use his PayPal account and everything will work like a charm.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SubscriptionVisa2001ThenPayPal1000Test extends BraintreeTestCase
{
    private const PLAN = "v4_biannually";


    /**
     * @return User
     * @throws Http400BadRequestException
     */
    public function testPayWithVisa2001()
    {
        // Configuration
        $processorCode = 2000;
        $processorType = "soft_declined";

        // Setup mocks
        $user = $this->getPristineCustomer();
        Passport::actingAs($user, ["registered"]);
        Notification::fake();
        SubscriptionUtils::fake();
        SubscriptionUtils::$ForceTrialPrice = $processorCode;

        $this->json("post", "/subscriptions", [
            "planId" => self::PLAN,
            "paymentMethodNonce" => "fake-valid-visa-nonce"
        ]);

        // Check response
        $this->assertJsonErrorResponse(400, "bad_request");
        $this->assertJsonResponseMatches([
            "data.processor.code" => $processorCode,
            //"data.processor.text" => $processorText,
            "data.processor.type" => $processorType
        ]);

        // Check that user is not premium
        $user->forgetBraintreeCustomerCache();
        $user->refresh();
        $this->assertFalse($user->premium);

        // Check that the payment method used is the user defaults
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $user->getBraintreeCustomer()->paymentMethods[0];
        $this->assertJsonDocumentMatches($paymentMethod->jsonSerialize(), [
            "cardType" => "Visa",
            "default" => true
        ]);

        // Check that the created subscription was cancelled
        /** @var Subscription $subscription */
        $subscription = $user->getBraintreeSubscriptions()->first();
        $this->assertEquals("Canceled", $subscription->status);

        // Check that the initial transaction failed
        $initialTransaction = Transaction::search([
            TransactionSearch::paymentMethodToken()->is($user->getBraintreeDefaultPaymentMethodToken())
        ])->firstItem();
        $this->assertJsonDocumentMatches($initialTransaction->jsonSerialize(), [
            "amount" => $processorCode . ".00",
            "status" => "processor_declined"
        ]);

        return $user;
    }


    /**
     * @depends testPayWithVisa2001
     *
     * @param User $user The user from the previous test.
     *
     * @throws Exception
     */
    public function testPayWithPayPal1000(User $user)
    {
        // Setup mocks
        Notification::fake();
        SubscriptionUtils::fake(false);
        Passport::actingAs($user, ["registered"]);

        $this->json("post", "/subscriptions", [
            "planId" => self::PLAN,
            "paymentMethodNonce" => "fake-paypal-billing-agreement-nonce"
        ]);

        // Check the response
        $startDateFormatted = SubscriptionUtils::getRegularPlanStartDate(self::PLAN)->format("c");
        $this->assertJsonResponseMatches([
            "id" => new StringStartsWith("sub_"),
            "status" => "Active",
            "firstBillingDate" => $startDateFormatted,
            "nextBillingDate" => $startDateFormatted,
            "planId" => self::PLAN,
            "transactions" => []
        ]);

        // Check the payment methods
        $paymentMethods = collect($user->braintree->paymentMethods);
        $payPalToken = $this->getJsonResponseValue("paymentMethodToken");

        /** @var PayPalAccount $payPal */
        $payPal = $paymentMethods
            ->filter(function ($p) use ($payPalToken) {
                return $p->token === $payPalToken;
            })
            ->first();
        $this->assertInstanceOf(PayPalAccount::class, $payPal);
        $this->assertTrue($payPal->isDefault());

        /** @var CreditCard $visa */
        $visa = $paymentMethods
            ->filter(function ($p) use ($payPalToken) {
                return $p->token !== $payPalToken;
            })
            ->first();
        $this->assertInstanceOf(CreditCard::class, $visa);
        $this->assertFalse($visa->isDefault());

        // Check that user is premium
        $user->refresh();
        $this->assertTrue($user->premium);

        // Check that a mail was sent
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $user, "mail");
    }
}

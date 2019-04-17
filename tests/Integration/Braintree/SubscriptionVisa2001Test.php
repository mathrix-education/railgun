<?php

namespace Tests\Integration\Braintree;

use App\Services\Braintree\Models\Subscription;
use App\Services\Braintree\Utils\SubscriptionUtils;
use Braintree\PaymentMethod;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Tests\Utils\NotificationsAssertionsTrait;

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
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SubscriptionVisa2001Test extends BraintreeTestCase
{
    use NotificationsAssertionsTrait;

    private const PLAN = "v4_biannually";


    /**
     * @throws Http400BadRequestException
     */
    public function testPayWithVisa2001(): void
    {
        // Configuration
        $processorCode = 2001;
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
        $this->assertResponseStatus(400);
        $response = $this->getJsonResponseContent();
        $this->assertEquals($processorCode, $response->data->processor->code);
        $this->assertEquals($processorType, $response->data->processor->type);

        // Check that user is not premium
        $user->forgetBraintreeCustomerCache();
        $user->refresh();
        $this->assertFalse($user->premium);

        // Check that the payment method used is the user defaults
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $user->getBraintreeCustomer()->paymentMethods[0];
        $this->assertEquals("Visa", $paymentMethod->cardType);
        $this->assertTrue($paymentMethod->default);

        // Check that the created subscription was cancelled
        /** @var Subscription $subscription */
        $subscription = $user->getBraintreeSubscriptions()->first();
        $this->assertEquals("Canceled", $subscription->status);

        // Check that the initial transaction failed
        /** @var Transaction $initialTransaction */
        $initialTransaction = Transaction::search([
            TransactionSearch::paymentMethodToken()->is($user->getBraintreeDefaultPaymentMethodToken())
        ])->firstItem();
        $this->assertEquals($processorCode . ".00", $initialTransaction->amount);
        $this->assertEquals("processor_declined", $initialTransaction->status);
    }
}

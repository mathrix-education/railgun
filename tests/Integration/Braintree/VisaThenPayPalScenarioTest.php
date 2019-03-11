<?php
namespace Tests\Integration\Braintree;

use App\Models\User;
use Braintree\PaymentMethod;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;

/**
 * Class VisaThenPayPalScenarioTest.
 *
 * Scenario:
 * A user will attempt to subscribe to the v4_biannually plan firstly use a credit card, but the process will fail
 * during the initial transaction. He will then use his PayPal account.
 *
 * The tests are the following:
 * testInvalidCreditCard: Using the fake-processor-declined-visa-nonce nonce, a customer will attempt to subscribe. We
 * will verify that he was not charged and that the created subscription was successfully cancelled.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since     1.0.0
 */
class VisaThenPayPalScenarioTest extends BraintreeTestCase
{
    public function testVisaBiannually()
    {
        Mail::fake();

        /** @var User $customer */
        $customer = User::query()
            ->where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->whereNull("stripe_id")
            ->inRandomOrder()
            ->firstOrFail();

        Passport::actingAs($customer, ["registered"]);

        $this->json("post", "/subscriptions", [
            "planId" => "v4_biannually",
            "paymentMethodNonce" => "fake-processor-declined-visa-nonce",
            "transactions" => []
        ]);

        $customer->forgetBraintreeCustomerCache();
        $customer->refresh();

        $this->assertTrue($customer->premium);
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "status" => "Active",
            "trialPeriod" => true
        ]);

        $responseData = $this->getResponseData();
        $paymentMethod = PaymentMethod::find($responseData->paymentMethodToken);
        $this->assertJsonDocumentMatches($paymentMethod->jsonSerialize(), [
            "cardType" => "Visa",
            "subscriptions[0].id" => $responseData->id
        ]);
    }
}

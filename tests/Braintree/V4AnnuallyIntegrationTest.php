<?php
namespace Tests\Braintree;

use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

/**
 * Class V4AnnuallyIntegrationTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class V4AnnuallyIntegrationTest extends BraintreeTestCase
{
    /**
     * POST /braintree/subscriptions
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeAnnuallyAsNonCustomer()
    {
        Notification::fake();

        $customer = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->firstOrFail();
        Passport::actingAs($customer, ["registered"]);

        $this->json("post", "/subscriptions", [
            "planId" => "v4_annually",
            "paymentMethodNonce" => "fake-valid-visa-nonce"
        ]);

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $customer->id,
            "braintree_id" => null
        ]);
        $this->assertTrue($customer->hasBraintreeSubscription("v4_annually"));
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $customer, "mail");
    }
}

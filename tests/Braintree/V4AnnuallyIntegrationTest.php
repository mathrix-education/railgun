<?php

namespace Tests\Braintree;

use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

/**
 * Class V4AnnuallyIntegrationTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class V4AnnuallyIntegrationTest extends BraintreeTestCase
{
    /**
     * POST /braintree/subscriptions.
     *
     * Test subscription to `v4_annually` as non-customer with 3 children
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeAnnuallyAsNonCustomer()
    {
        Notification::fake();

        $customer = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->inRandomOrder()
            ->firstOrFail();
        Passport::actingAs($customer, ["registered"]);

        $childEmails = User::inRandomOrder()
            ->whereNull("braintree_id")
            ->limit(3)
            ->get()
            ->pluck("email");

        $this->json("post", "/subscriptions", [
            "planId" => "v4_annually",
            "family" => $childEmails,
            "paymentMethodNonce" => "fake-valid-visa-nonce",
        ]);

        $children = User::whereIn("email", $childEmails)
            ->get();

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $customer->id,
            "braintree_id" => null,
        ]);
        $this->assertTrue($customer->hasBraintreeSubscription("v4_annually"));
        $this->assertNotificationSentToVia(TransactionSuccessNotification::class, $customer, "mail");

        /** @var User $child */
        foreach ($children as $child) {
            $child->append(["premium", "braintree"]);
            $this->assertTrue($child->premium);
        }
    }
}

<?php
namespace Tests\Stripe;

use App\Models\Purchase;
use App\Models\User;
use App\Utils\StripeUtils;
use Carbon\Carbon;
use Laravel\Cashier\Subscription;

/**
 * Class InvoicePaymentSucceededTest
 * Test the invoice.payment_succeeded event
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Stripe
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class InvoicePaymentSucceededTest extends StripeWebhook
{
    protected const EVENT = "invoice.payment_succeeded";


    /**
     * Make the mock data
     *
     * @return mixed
     */
    public function factory(): array
    {
        parent::factory();

        /** @var Subscription $subscription */
        $subscription = Subscription::inRandomOrder()
            ->whereNull("ends_at")
            ->firstOrFail();
        $subscriptionStripeId = $subscription->stripe_id;

        /** @var User $user */
        $user = $subscription->user;
        $customerStripeId = $user->stripe_id;

        $stripePlan = $subscription->stripe_plan;
        $planStaticData = StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan];

        $nowTimestamp = Carbon::now()->timestamp;

        $invoice = [
            "id" => $this->generateStripeId("in"),
            "amount_due" => $planStaticData["price_per_period"],
            "amount_remaining" => $planStaticData["price_per_period"],
            "charge" => $this->generateStripeId("ch"),
            "customer" => $customerStripeId,
            "date" => $nowTimestamp,
            "subscription" => $subscriptionStripeId,
            "total" => $planStaticData["price_per_period"],
            "webhooks_delivered_at" => $nowTimestamp
        ];

        $lines = [
            "url" => "/v1/invoices/{$invoice["id"]}/lines"
        ];

        $plan = [
            "id" => $stripePlan,
            "amount" => $planStaticData["price_per_period"],
            "interval" => $planStaticData["interval"],
            "interval_count" => $planStaticData["interval_count"],
            "name" => $planStaticData["name"]
        ];

        $this->mockData = array_replace_recursive($this->mockData, [
            "data" => [
                "object" => array_replace_recursive($invoice, [
                    "lines" => array_replace_recursive($lines, [
                        "data" => [
                            ["plan" => $plan]
                        ]
                    ])
                ])
            ]
        ]);

        return $this->mockData;
    }


    /**
     * Test the mock
     *
     * @throws \Exception
     */
    public function testMockPremiumSubscription()
    {
        $this->json("post", "/stripe/webhook", $this->factory());

        $response = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertInDatabase("users", ["stripe_id" => $response->data->customer_id]);
        $subscription = Subscription::where("stripe_id", "=", $response->data->subscription_id)->first();
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertLogged(
            "user_payment_succeed_for_premium",
            User::whereStripeId($response->data->customer_id)->first()
        );
        $this->assertNull($subscription->ends_at);
    }


    /**
     * Test the mock
     *
     * @throws \Exception
     */
    public function testMockCharge()
    {
        $purchase = Purchase::random();

        $mock = $this->factory();
        $mock["data"]["object"]["charge"] = $purchase->stripe_id;

        $this->json("post", "/stripe/webhook", $mock);

        $response = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertInDatabase("users", ["stripe_id" => $response->data->customer_id]);
        $this->assertLogged(
            "user_payment_succeed_for_{$purchase->purchasable_type}",
            User::whereStripeId($response->data->customer_id)->first()
        );
    }
}

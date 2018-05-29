<?php
namespace Tests\Stripe;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use App\Utils\StripeUtils;
use Carbon\Carbon;
use Laravel\Cashier\Subscription;

/**
 * Class CustomerSubscriptionDeletedTest
 * Test the customer.subscription.deleted event
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Stripe
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class CustomerSubscriptionDeletedTest extends StripeWebhook
{
    protected const EVENT = "customer.subscription.deleted";


    /**
     * Make the mock data
     *
     * @return mixed
     * @throws Http400InvalidRequestException
     */
    public function factory(): array
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::inRandomOrder()
            ->whereNull("ends_at")
            ->firstOrFail();
        /** @var User $user */
        $user = $subscription->user;

        $stripePlan = $subscription->stripe_plan;
        $planStaticData = StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan];
        $isSpecialPlan = in_array($stripePlan, ["v3_annually", "v3_biannually"]);

        $now = Carbon::now();
        $nowTimestamp = $now->timestamp;
        $createdTimestamp = $subscription->created_at->timestamp;
        $refreshTimestamp = $isSpecialPlan ? StripeUtils::getStripePlanEndDate($stripePlan)->timestamp : $now->copy()
            ->addMonth()->timestamp;

        $subscriptionMock = [
            "id" => $subscription->stripe_id,
            "billing_cycle_anchor" => $refreshTimestamp,
            "created" => $createdTimestamp,
            "current_period_end" => $refreshTimestamp,
            "current_period_start" => $createdTimestamp,
            "ended_at" => $nowTimestamp,
            "customer" => $user->stripe_id,
            "start" => $createdTimestamp,
            "trial_end" => $refreshTimestamp,
            "trial_start" => $createdTimestamp
        ];

        $items = [
            [
                "created" => $createdTimestamp,
                "plan" => [
                    "id" => $stripePlan,
                    "amount" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_period"],
                    "interval" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["interval"],
                    "interval_count" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["interval_count"],
                ],
                "subscription" => $subscription->stripe_id
            ]
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
                "object" => array_replace_recursive($subscriptionMock, [
                    "items" => array_replace_recursive($items, [
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
     * @return mixed
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function testMock()
    {
        $this->json("post", "/stripe/webhook", $this->factory());

        $response = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertInDatabase("users", ["stripe_id" => $response->data->customer_id]);
        $subscription = Subscription::where("stripe_id", "=", $response->data->subscription_id)->first();
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertNotNull($subscription->ends_at);
    }
}

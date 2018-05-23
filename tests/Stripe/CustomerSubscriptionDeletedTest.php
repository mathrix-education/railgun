<?php
namespace Tests\Stripe;

use App\Exceptions\Http400InvalidRequestException;
use App\Utils\StripeUtils;
use Carbon\Carbon;
use Laravel\Cashier\Subscription;

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

        $stripePlan = $subscription->stripe_plan;
        $isSpecialPlan = in_array($stripePlan, ["v3_annually", "v3_biannually"]);

        $now = Carbon::now();
        $nowTimestamp = $now->timestamp;
        $createdTimestamp = $subscription->created_at->timestamp;
        $refreshTimestamp = $isSpecialPlan ? StripeUtils::getStripePlanEndDate($stripePlan) : $now->copy()->addMonth();

        $this->mockData["created"] = $nowTimestamp;
        $this->mockData["api_version"] = self::STRIPE_API_VERSION;

        $dataObject = [
            "billing_cycle_anchor" => $refreshTimestamp,
            "created" => $createdTimestamp,
            "current_period_end" => $refreshTimestamp,
            "current_period_start" => $createdTimestamp,
            "start" => $createdTimestamp,
            "trial_end" => $refreshTimestamp,
            "trial_start" => $createdTimestamp
        ];
        $this->mockData["data"]["object"] = array_replace_recursive(
            $this->mockData["data"]["object"],
            $dataObject
        );


        $this->mockData["data"]["object"]["items"]["data"][0] = array_replace_recursive(
            $this->mockData["data"]["object"]["items"]["data"][0],
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
        );

        $dataObjectPlan = [
            "amount" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_period"],
            "interval" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["interval"],
            "interval_count" => StripeUtils::ALLOWED_STRIPE_PLANS[$stripePlan]["interval_count"],
        ];
        $this->mockData["data"]["object"]["plan"] = array_replace_recursive(
            $this->mockData["data"]["object"]["plan"],
            $dataObjectPlan
        );

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
        foreach ($response->data->canceled_subscription_ids as $stripeId) {
            $subscription = Subscription::where("stripe_id", "=", $stripeId)->first();
            $this->assertInstanceOf(Subscription::class, $subscription);
            $this->assertNotNull($subscription->ends_at);
        }
    }
}

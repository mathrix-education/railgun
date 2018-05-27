<?php
namespace Tests\Stripe;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\BaseTestCase;

/**
 * Class PremiumSubscriptionTest
 *
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Stripe
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class PremiumSubscriptionTest extends BaseTestCase
{
    /**
     * @param string $stripePlan
     *
     * @param array $data
     *
     * @return User
     */
    private function subscribeToPlan(string $stripePlan, array $data = []): User
    {
        /** @var User $user */
        $user = User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        $this->call("post", "/stripe/subscribe", array_merge([
            "stripe_token" => "tok_visa",
            "stripe_plan" => $stripePlan
        ], $data));

        return $user;
    }


    /**
     * @param string $stripePlan
     */
    private function subscribeToPlanSuccess(string $stripePlan)
    {
        $user = $this->subscribeToPlan($stripePlan);
        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $user->id,
            "name" => "premium",
            "stripe_plan" => $stripePlan
        ]);
    }


    /**
     * Test the monthly subscription
     */
    public function testSubscriptionMonthly()
    {
        $this->subscribeToPlanSuccess("v3_monthly");
    }


    /**
     * Test the annually subscription
     */
    public function testSubscriptionAnnually()
    {
        $this->subscribeToPlanSuccess("v3_annually");
    }


    /**
     * Test the biannually subscription
     */
    public function testSubscriptionBiannually()
    {
        $this->subscribeToPlanSuccess("v3_biannually");
    }


    /**
     * Test subscription with null token and coupon
     */
    public function testSubscriptionTokenAndCouponNull()
    {
        $this->subscribeToPlan("v3_monthly", [
            "stripe_token" => null,
            "stripe_coupon" => null
        ]);

        $this->assertHttp400InvalidRequest();
    }


    /**
     * Test subscription with null token and coupon
     */
    public function testSubscriptionInvalidPlan()
    {
        $this->subscribeToPlan("invalid_plan");

        $this->assertHttp400InvalidRequest();
    }
}

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
     */
    private function subscribeToPlan(string $stripePlan)
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => $stripePlan
        ]);

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
        $this->subscribeToPlan("v3_monthly");
    }


    /**
     * Test the annually subscription
     */
    public function testSubscriptionAnnually()
    {
        $this->subscribeToPlan("v3_annually");
    }


    /**
     * Test the biannually subscription
     */
    public function testSubscriptionBiannually()
    {
        $this->subscribeToPlan("v3_biannually");
    }
}

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
     * Test the monthly subscription
     */
    public function testSubscriptionMonthly()
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->firstOrFail();
        Passport::actingAs($user, [$user->type]);

        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => "v3_monthly"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $user->id,
            "name" => "premium",
            "stripe_plan" => "v3_monthly"
        ]);
    }


    /**
     * Test the monthly subscription
     */
    public function testSubscriptionSpecial()
    {
        $user = User::where("type", "=", "registered")
            ->whereNull("stripe_id")
            ->firstOrFail();
        $stripePlan = $this->faker->randomElement(["v3_annually", "v3_biannually"]);
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
}

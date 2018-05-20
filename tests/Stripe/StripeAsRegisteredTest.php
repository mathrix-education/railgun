<?php
namespace Tests\Stripe;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsPublicTest
 * Test quotes as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Quotes
 */
class StripeAsRegisteredTest extends ScopeTestCase
{
    /**
     * Test the monthly subscription
     */
    public function testSubscription()
    {
        $stripePlan = $this->faker->randomElement(["v3_monthly", "v3_annually", "v3_biannually"]);
        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => $stripePlan
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $this->user->id,
            "name" => "premium",
            "stripe_plan" => $stripePlan
        ]);
    }


    /**
     * GET /stripe/estimate
     *
     * @throws \Exception
     */
    public function testEstimate()
    {
        $this->call("get", "/stripe/estimate/v3_annually");
        $response = $this->getResponseData() ;

        $this->assertHttp200Success();
        $this->assertTrue(isset($response->price));
        $this->assertTrue(isset($response->end_date));
    }
}

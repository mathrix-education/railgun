<?php
namespace Tests\Stripe;

use Tests\ScopeTestCase;

/**
 * Class CountriesAsPublicTest
 * Test quotes as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix SA
 * @version 1.0.0
 * @package Tests\Quotes
 */
class StripeAsRegisteredTest extends ScopeTestCase
{
    /**
     * Test the monthly subscription
     */
    public function testMonthly()
    {
        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => "v3_monthly"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $this->user->id,
            "name" => "premium",
            "stripe_plan" => "v3_monthly"
        ]);
    }


    /**
     * Test the annually subscription
     */
    public function testAnnually()
    {
        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => "v3_annually"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $this->user->id,
            "name" => "premium",
            "stripe_plan" => "v3_annually"
        ]);
    }


    /**
     * Test the biannually subscription
     */
    public function testBiannually()
    {
        $this->call("post", "/stripe/subscribe", [
            "stripe_token" => "tok_visa",
            "stripe_plan" => "v3_biannually"
        ]);

        $this->assertHttp200Success();
        $this->assertInDatabase("subscriptions", [
            "user_id" => $this->user->id,
            "name" => "premium",
            "stripe_plan" => "v3_biannually"
        ]);

    }
}

<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Testing\Traits\DebugTrait;
use Tests\APIStatelessTestCase;

/**
 * Class SubscriptionsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionsTest extends APIStatelessTestCase
{
    use DebugTrait;

    /** @var User */
    private $user;


    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->user->createStripeCustomer();
        JWT::actingAs($this->user);
    }


    /**
     * POST /subscriptions
     * Test v5_monthly with in invalid CVC.
     */
    public function testPostMonthlyCVCFail(): void
    {
        $this->json("post", "/subscriptions", [
            "token" => "tok_cvcCheckFail",
            "plan" => "v5_monthly"
        ]);

        $this->assertOpenAPIResponse($this->response);
        $this->assertFalse($this->user->premium);
    }


    /**
     * POST /subscriptions
     * Test v5_monthly with a card which does not require 3DS2.
     */
    public function testPostMonthlyNo3DS(): void
    {
        $this->json("post", "/subscriptions", [
            "token" => "tok_fr",
            "plan" => "v5_monthly"
        ]);

        $this->assertOpenAPIResponse($this->response);
        $this->assertTrue($this->user->premium);
    }


    /**
     * POST /subscriptions
     * Test v5_monthly with a card which does require 3DS2.
     */
    public function testPostMonthly3DS2(): void
    {
        $this->json("post", "/subscriptions", [
            "token" => "tok_threeDSecure2Required",
            "plan" => "v5_monthly"
        ]);

        $this->assertOpenAPIResponse($this->response);
        $this->assertFalse($this->user->premium);
    }


    /**
     * POST /subscriptions
     * Test v5_annually with a card which does not require 3DS2.
     */
    public function testPostAnnuallyNo3DS(): void
    {
        $this->json("post", "/subscriptions", [
            "token" => "tok_fr",
            "plan" => "v5_annually"
        ]);

        $this->assertOpenAPIResponse($this->response);
        $this->assertTrue($this->user->premium);
    }


    /**
     * POST /subscriptions
     * Test v5_annually with a card which does require 3DS2.
     */
    public function testPostAnnually3DS2(): void
    {
        $this->json("post", "/subscriptions", [
            "token" => "tok_threeDSecure2Required",
            "plan" => "v5_annually"
        ]);

        $this->assertOpenAPIResponse($this->response);
        $this->assertTrue($this->user->premium);
    }
}

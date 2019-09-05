<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\JWT\Auth\JWT;
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
    /**
     * Given the test data set.
     *
     * @return array
     *
     * @see SubscriptionsTest::testPost()
     */
    public function postDataProvider()
    {
        return [
            "monthly_cvc_fail" => ["v5_monthly", "tok_cvcCheckFail", 422, false],
            "monthly_no_3ds2" => ["v5_monthly", "tok_fr", 200, true],
            "monthly_3ds2" => ["v5_monthly", "tok_threeDSecure2Required", 200, false],
            "annually_no_3ds2" => ["v5_annually", "tok_fr", 200, true],
            "annually_3ds2" => ["v5_annually", "tok_threeDSecure2Required", 200, false]
        ];
    }


    /**
     * POST /subscriptions
     * Test subscription creation in various cases.
     *
     * @param string $plan The plan to subscribe.
     * @param string $token The card token.
     * @param int $expectedStatus The HTTP expected status.
     * @param bool $expectedPremium The user expected premium state.
     *
     * @dataProvider postDataProvider
     */
    public function testPost(string $plan, string $token, int $expectedStatus, bool $expectedPremium)
    {
        $this->requester = factory(User::class)->create();
        JWT::actingAs($this->requester);

        $this->json("post", "/subscriptions", [
            "plan" => $plan,
            "card" => [
                "token" => $token,
            ]
        ]);

        $this->assertResponseStatus($expectedStatus);
        $this->assertOpenAPIResponse($this->response);
        $this->assertEquals($expectedPremium, $this->requester->premium);
    }
}

<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatefulTestCase;
use Tests\APIStatelessTestCase;

/**
 * Class SubscriptionsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionCreationTest extends APIStatelessTestCase
{
    /**
     * Given the test data set.
     *
     * @return array
     *
     * @see SubscriptionCreationTest::testPost()
     */
    public function postDataProvider()
    {
        return [
            // Subscription is active
            "monthly_no_3ds2" => ["v5_monthly", "tok_fr", 200, true],
            "annually_no_3ds2" => ["v5_annually", "tok_fr", 200, true],
            // 3D Secure 2 has been triggered
            "monthly_tok_threeDSecure2Required" => ["v5_monthly", "tok_threeDSecure2Required", 202, false],
            "annually_tok_threeDSecure2Required" => ["v5_annually", "tok_threeDSecure2Required", 202, false],
            // Errors
            "cvc_fail" => ["v5_monthly", "tok_cvcCheckFail", 422, false],
            "tok_chargeDeclined" => ["v5_monthly", "tok_chargeDeclined", 422, false]
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

        $response = json_decode($this->response->getContent());
    }
}

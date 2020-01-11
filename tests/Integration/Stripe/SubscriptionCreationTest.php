<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use App\Models\Product;
use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;
use function factory;

class SubscriptionCreationTest extends APIStatelessTestCase
{
    /**
     * Given the test data set.
     *
     * @see SubscriptionCreationTest::testPost()
     *
     * @return array The dataset for the test.
     */
    public function postDataProvider(): array
    {
        return $this->cartesian([
            'plans'  => ['v5_monthly', 'v5_annually'],
            'tokens' => [
                ['tok_fr', 200, true],
                ['tok_threeDSecure2Required', 202, false],
                ['tok_cvcCheckFail', 422, false],
                ['tok_chargeDeclined', 422, false],
            ],
        ]);
    }

    /**
     * POST /subscriptions
     * Test subscription creation in various cases.
     *
     * @param string $plan            The plan to subscribe.
     * @param string $token           The card token.
     * @param int    $expectedStatus  The HTTP expected status.
     * @param bool   $expectedPremium The user expected premium state.
     *
     * @dataProvider postDataProvider
     */
    public function testPost(string $plan, string $token, int $expectedStatus, bool $expectedPremium): void
    {
        $this->requester = factory(User::class)->create();
        JWT::actingAs($this->requester);

        $this->json(
            'post',
            '/subscriptions',
            [
                'plan' => $plan,
                'card' => ['token' => $token],
            ]
        );

        $this->assertResponseStatus($expectedStatus);
        $this->assertOpenAPIResponse($this->response);
        $this->assertEquals($expectedPremium, $this->requester->premium);
    }

    public function testPostWithProductId(): void
    {
        $this->requester = factory(User::class)->create();
        JWT::actingAs($this->requester);

        /** @var Product $product */
        $product = Product::query()
            ->inRandomOrder()
            ->where('type', '=', 'pack')
            ->where('buyable', '=', true)
            ->firstOrFail();

        $this->json(
            'post',
            '/subscriptions',
            [
                'plan'       => 'v5_annually',
                'card'       => ['token' => 'tok_fr'],
                'product_id' => $product->id,
            ]
        );

        $this->assertResponseStatus(200);
        $this->assertOpenAPIResponse($this->response);
        $this->assertTrue($this->requester->premium);
    }
}

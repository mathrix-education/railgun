<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use Tests\Utils\CartInputGenerator;
use function array_merge;

class CartIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @param mixed[] $cartInput The CartInput GraphQL input.
     */
    public function executeCart(array $cartInput): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($cartInput: CartInput!) {
                cart(cart: $cartInput) {
                    lines {
                        description
                        price
                    }
                    subTotal
                    total
                }
            }
            GRAPHQL,
            ['cartInput' => $cartInput],
        );
    }

    public function testInvalidCouponCode(): void
    {
        $this->executeCart(array_merge(CartInputGenerator::make('trainings'), ['coupon' => 'wrong']));

        $this->assertResponseOk();
        $this->assertErrorCode('INVALID_COUPON_CODE');
    }

    public function testPlan(): void
    {
        $this->executeCart(['plan' => 'laser_month']);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

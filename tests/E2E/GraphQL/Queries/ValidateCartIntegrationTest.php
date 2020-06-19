<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Query validateCart
 */
class ValidateCartIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private function executeValidateCart(array $cartInput): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($cart: CartInput!) {
                validateCart(cart: $cart) {
                    lines {
                        description
                        price
                    }
                    subTotal
                    total
                }
            }
            GRAPHQL,
            ['cart' => $cartInput],
        );
    }

    public function testLol(): void
    {
        $this->executeValidateCart([
            'chapters'  => [45, 46, 47],
            'packs'     => [1],
            'trainings' => [10],
            // 'coupon'    => Coupon::random()->id,
        ]);

        $this->dump();
    }
}

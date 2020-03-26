<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] Query validateCart
 * @coversNothing
 */
class ValidateCartIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    query($packIds: [ID!], $trainingIds: [ID!], $premium: Boolean, $coupon: ID) {
        validateCart(
            cart: {
                packIds: $packIds
                trainingIds: $trainingIds
                premium: $premium
                coupon: $coupon
            }
        ) {
            packs {
                name
                price
            }
            trainings {
                name
                price
            }
            coupon {
                id
            }
            subTotal
            total
        }
    }
    GRAPHQL;

    /**
     * @testdox returns the validated cart
     */
    public function testValid(): void
    {
        $this->actingAsNewUser();

        $this->graphQL(
            self::QUERY,
            ['trainingIds' => [1, 2, 3]]
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function dd;
use function factory;

/**
 * @coversNothing
 */
class ValidateCartIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    query($packIds: [ID!], $trainingIds: [ID!], $premium: Boolean, $coupon: ID) {
        validateCart(
            packIds: $packIds
            trainingIds: $trainingIds
            premium: $premium
            coupon: $coupon
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

    public function testValid()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->graphQL(
            self::QUERY,
            ['trainingIds' => [1, 2, 3]]
        );

        dd($this->getContent());
    }
}

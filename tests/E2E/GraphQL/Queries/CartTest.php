<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Billing\Stripe\Repositories\PricesRepository;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\CartInputGenerator;
use function array_merge;

class CartTest extends GraphQLTestCase
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
        $this->executeCart(['plans' => [PricesRepository::PREMIUM_MONTH]]);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    public function testCombo(): void
    {
        $this->executeCart(['plans' => [PricesRepository::PREMIUM_MONTH, PricesRepository::ACADEMY_MONTH]]);

        $this->dump();
        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

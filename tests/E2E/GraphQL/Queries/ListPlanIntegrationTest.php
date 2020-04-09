<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Query listPlans
 */
class ListPlanIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox lists the available plans.
     */
    public function testList(): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query {
                listPlans {
                    id
                    period
                    price_per_period
                    price_per_month
                    prorated
                    proration_price
                    proration_anchor
                }
            }
            GRAPHQL,
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

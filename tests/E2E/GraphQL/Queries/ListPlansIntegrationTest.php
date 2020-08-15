<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Query listPlans
 */
class ListPlansIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox lists the available plans
     */
    public function testList(): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query {
                listPlans {
                    id
                    name
                    currency
                    price
                    period {
                        unit
                        length
                    }
                }
            }
            GRAPHQL,
        );

        $this->dump();

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

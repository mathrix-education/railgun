<?php

namespace Tests\E2E\GraphQL\Queries;

use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

class CheckoutProductsIntegrationTest extends GraphQLIntegrationTestCase
{
    public function executeCheckouProduct()
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query {
                checkoutProducts {
                    id
                }
            }
            GRAPHQL
        );
    }

    public function testLol()
    {
        $this->actingAs();
        $this->executeCheckouProduct();

        $this->dump();
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Billing\Stripe\Models\ModeMachine;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function count;

class ListPlansTest extends GraphQLTestCase
{
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

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount(count(ModeMachine::PRICES), $this->getData('listPlans'));
    }
}

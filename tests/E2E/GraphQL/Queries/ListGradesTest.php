<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\DB;
use Tests\E2E\GraphQL\GraphQLTestCase;

class ListGradesTest extends GraphQLTestCase
{
    protected function execute(): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query {
                listGrades {
                    id
                    name
                    slug
                    order
                    visible
                    parent {
                        id
                        name
                        slug
                        order
                        visible
                    }
                }
            }
            GRAPHQL,
        );
    }

    public function testResponseOk(): void
    {
        $this->execute();

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount(
            DB::table('grades')->count(),
            $this->getData('listGrades'),
        );
    }
}

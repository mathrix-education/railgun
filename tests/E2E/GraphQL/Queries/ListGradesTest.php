<?php

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
                listGrade {
                    id
                    name
                    slug
                    order
                    visible
                    created_at
                    updated_at
                    parent {
                        id
                        name
                        slug
                        order
                        visible
                        created_at
                        updated_at
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
            $this->getData(),
        );
    }
}

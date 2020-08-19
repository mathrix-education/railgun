<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\DB;
use Railgun\Models\Grade;
use Tests\E2E\GraphQL\GraphQLTestCase;

class ListDisciplinesTest extends GraphQLTestCase
{
    public function testListAllDisciplines(): void
    {
        $this->executeListDisciplines();

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount(
            DB::table('disciplines')->count(),
            $this->getData('listDisciplines'),
        );
    }

    /**
     * @param mixed[] $variables The listDisciplines variables
     */
    public function executeListDisciplines(array $variables = []): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($gradeSlug: String, $withInvisible: Boolean) {
                listDisciplines(gradeSlug: $gradeSlug, withInvisible: $withInvisible) {
                    id
                    name
                }
            }
            GRAPHQL,
            $variables,
        );
    }

    /**
     * @testWith [false]
     *           [true]
     *
     * @param bool $withInvisible Include invisible disciplines
     */
    public function testVisibleDisciplines(bool $withInvisible): void
    {
        $this->executeListDisciplines([
            'gradeSlug'     => Grade::random()->slug,
            'withInvisible' => $withInvisible,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}

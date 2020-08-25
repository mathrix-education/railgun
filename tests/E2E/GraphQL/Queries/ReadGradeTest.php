<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\DB;
use Railgun\Models\Grade;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function collect;

class ReadGradeTest extends GraphQLTestCase
{
    public function testDisciplinesOrdered(): void
    {
        /** @var Grade $grade */
        $grade = Grade::query()->inRandomOrder()->has('disciplines')->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readGrade(id: $id) {
                    id
                    name
                    disciplines {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            ['id' => $grade->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        $actualOrder   = collect($this->getData('disciplines'))
            ->pluck('id')
            ->toArray();
        $expectedOrder = DB::table('discipline_grade')
            ->where('grade_id', '=', $grade->id)
            ->orderBy('order')
            ->pluck('discipline_id')
            ->toArray();

        $this->assertEquals($expectedOrder, $actualOrder);
    }
}

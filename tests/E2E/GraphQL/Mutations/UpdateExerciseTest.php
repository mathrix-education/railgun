<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class UpdateExerciseTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @link    https://github.com/nuwave/lighthouse/issues/1264
     *
     * @ticket #243
     */
    public function testUpdateExercise(): void
    {
        /** @var Subject $subject */
        $subject = factory(Subject::class)->create(['premium' => false]);
        /** @var Exercise $exercise */
        $exercise  = factory(Exercise::class)->create(['subject_id' => $subject->id]);
        $variables = Arr::except(
            factory(Exercise::class)->raw([
                'id'         => $exercise->id,
                'subject_id' => $subject->id,
            ]),
            ['protected'],
        );

        $this->withPermissions(['exercises:write', 'exercises:write:visible']);
        $this->executeUpdateExercise([
            'input' => Arr::except($variables, ['premium', 'created_at', 'updated_at']),
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($variables['content'][0]['uuid'], $this->getData('content.0.uuid'));

        $this->assertCount(
            1,
            DB::table('exercises')
                ->whereRaw('JSON_EXTRACT(content, \'$[0].uuid\') = ?', [$variables['content'][0]['uuid']])
                ->get(),
        );
    }

    /**
     * @param mixed[] $variables The updateExercise variables.
     */
    private function executeUpdateExercise(array $variables = []): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateExercise!) {
                updateExercise(input: $input) {
                    id
                    content
                    premium
                    updated_at
                }
            }
            GRAPHQL,
            $variables,
        );
    }
}

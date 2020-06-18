<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation updateMCQExercise
 */
class UpdateMCQExerciseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @param mixed[] $variables The updateExercise variables.
     */
    private function executeUpdateExercise(array $variables = []): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateMCQExercise!) {
                updateMCQExercise(input: $input) {
                    id
                    questions
                    solutions
                    explanations
                    choices
                    premium
                    updated_at
                }
            }
            GRAPHQL,
            $variables,
        );
    }

    /**
     * @testdox updates an existing exercise
     *
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
            ['type'],
        );

        $this->withPermissions(['exercises:write', 'exercises:write:visible']);
        $this->executeUpdateExercise([
            'input' => Arr::except($variables, ['premium', 'created_at', 'updated_at']),
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($variables['questions'][0][0]['uuid'], $this->getData('questions.0.0.uuid'));

        $this->assertCount(
            1,
            DB::table('exercises')
                ->whereRaw('JSON_EXTRACT(questions, \'$[0][0].uuid\') = ?', [$variables['questions'][0][0]['uuid']])
                ->get(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_merge;
use function factory;

/**
 * @testdox Mutation createExercise
 */
class CreateExerciseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @param mixed[] $variables The createExercise variables.
     */
    private function executeCreateExercise(array $variables = []): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            fragment BasicExercise on Exercise {
                id
                questions
                solutions
                explanations
                choices
                premium
                updated_at
                __typename
            }

            mutation(
                $type: ExerciseType!
                $questions: [Silicate!]!
                $choices: [SilicateList!]!
                $solutions: [IntList!]!
                $explanations: [SilicateList!]!
                $order: Int
                $premium: Boolean!
                $visible: Boolean!
                $subject_id: ID!
            ) {
                createExercise(
                    type: $type
                    questions: $questions
                    choices: $choices
                    solutions: $solutions
                    explanations: $explanations
                    order: $order
                    premium: $premium
                    visible: $visible
                    subject_id: $subject_id
                ) {
                    ...BasicExercise
                }
            }
            GRAPHQL,
            $variables,
        );
    }

    /**
     * @testdox creates a new exercise.
     *
     * @ticket #243
     */
    public function test(): void
    {
        $variables = array_merge(
            factory(Exercise::class)->raw(),
            ['type' => 'MCQ', 'premium' => false],
        );
        $this->actingAsNewUser('exercises:create');

        $this->executeCreateExercise($variables);

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

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox [E2E] Mutation updateExercise
 */
class UpdateExerciseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @param mixed[] $variables The updateExercise variables.
     */
    private function executeUpdateExercise(array $variables = []): void
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
                $id: ID!
                $questions: [Silicate!]
                $choices: [SilicateList!]
                $solutions: [IntList!]
                $explanations: [SilicateList!]
                $order: Int
                $premium: Boolean
                $visible: Boolean
                $subject_id: ID
            ) {
                updateExercise(
                    id: $id
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
     * @testdox updates an existing exercise.
     *
     * @link    https://github.com/nuwave/lighthouse/issues/1264
     *
     * @ticket #243
     */
    public function testUpdateExercise(): void
    {
        /** @var Exercise $exercise */
        $exercise  = factory(Exercise::class)->create();
        $variables = factory(Exercise::class)->raw(['id' => $exercise->id, 'premium' => false]);

        $this->actingAsNewUser('exercises:update');
        $this->executeUpdateExercise($variables);

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

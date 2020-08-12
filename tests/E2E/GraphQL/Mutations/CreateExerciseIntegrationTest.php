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
 * @testdox Mutation createMCQExercise
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
            mutation($input: CreateExercise!) {
                createExercise(input: $input) {
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

    /**
     * @testdox creates a new exercise
     *
     * @ticket #243
     */
    public function testCreate(): void
    {
        /** @var Subject $subject */
        $subject   = factory(Subject::class)->create(['premium' => false]);
        $variables = Arr::except(
            factory(Exercise::class)->raw(['subject_id' => $subject->id]),
            ['id', 'type', 'premium', 'created_at', 'updated_at'],
        );
        $this->withPermissions(['exercises:write', 'exercises:write:visible']);

        $this->executeCreateExercise(['input' => $variables]);

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
}

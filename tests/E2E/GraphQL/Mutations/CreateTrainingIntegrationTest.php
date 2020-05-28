<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Grade;
use Railgun\Models\Training;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_merge;
use function factory;

/**
 * @testdox Mutation createTraining
 */
class CreateTrainingIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @param mixed[] $input The CreateTraining input.
     */
    private function executeCreateTraining(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateTraining!) {
                createTraining(input: $input) {
                    id
                    name
                    grades {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    /**
     * @testdox create a new training with an attached grade
     */
    public function testCreateWithGrade(): void
    {
        $grade = Grade::random();
        $input = array_merge(
            Arr::except(factory(Training::class)->raw(), ['created_at', 'updated_at']),
            ['grades' => ['replace' => [$grade->id]]],
        );

        $this->withPermissions('trainings:write');
        $this->executeCreateTraining($input);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->assertEquals($grade->id, $this->getData('grades.0.id'));
        $this->seeInDatabase('grade_training', [
            'training_id' => $this->getData('id'),
            'grade_id'    => $grade->id,
        ]);
    }
}

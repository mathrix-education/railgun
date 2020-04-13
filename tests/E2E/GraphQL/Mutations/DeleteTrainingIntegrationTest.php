<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Section;
use Railgun\Models\Training;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation deleteTraining
 */
class DeleteTrainingIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox fails to delete a course when it is not empty.
     */
    public function testDeleteNonEmpty(): void
    {
        /** @var Training $training */
        $training = factory(Training::class)->create();
        factory(Section::class)->create(['training_id' => $training->id]);

        $this->actingAsNewUser('trainings:delete');

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($id: ID!) {
                deleteTraining(id: $id) {
                    id
                }
            }
            GRAPHQL,
            ['id' => $training->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('trainings', ['id' => $training->id]);
    }
}

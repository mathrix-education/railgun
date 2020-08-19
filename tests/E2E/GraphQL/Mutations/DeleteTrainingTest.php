<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Section;
use Railgun\Models\Training;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class DeleteTrainingTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testDeleteNonEmpty(): void
    {
        /** @var Training $training */
        $training = factory(Training::class)->create();
        factory(Section::class)->create(['training_id' => $training->id]);

        $this->withPermissions('trainings:delete');

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
        $this->assertErrorCode('FOREIGN_KEY_CONSTRAINT');
        $this->assertErrorCategory('database');
        $this->seeInDatabase('trainings', ['id' => $training->id]);
    }
}

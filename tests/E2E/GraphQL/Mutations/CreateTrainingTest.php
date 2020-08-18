<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Grade;
use Railgun\Models\Training;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function array_merge;
use function factory;

class CreateTrainingTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testCreateTraining(): void
    {
        $this->withPermissions('trainings:write');

        $payload = Arr::except(
            factory(Training::class)->raw(),
            ['created_at', 'updated_at'],
        );

        $this->executeCreateTraining($payload);

        /** @var Training $training */
        $training = Training::query()->findOrFail($this->getData('id'));

        $this->assertNotNull($training->stripe_product_id);
        $this->assertNotNull($training->stripe_price_id);
    }

    /**
     * @param mixed[] $input The CreateTraining input.
     */
    protected function executeCreateTraining(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateTraining!) {
                createTraining(input: $input) {
                    id
                    name
                    slug
                    duration
                    content
                    image
                    available
                    price
                    order
                    visible
                    created_at
                    updated_at
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

        /** @var Training $training */
        $training = Training::query()->findOrFail($this->getData('id'));

        $this->assertNotNull($training->stripe_product_id);
        $this->assertNotNull($training->stripe_price_id);
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Label;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation updateCourse
 */
class UpdateCourseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox fails to connect a label twice to a course.
     *
     * @ticket #293
     */
    public function testLabelConnectedTwice(): void
    {
        $this->actingAsNewUser(['scopes' => ['courses:update', 'labels:read']]);

        /** @var Course $course */
        $course = factory(Course::class)->create();
        /** @var Label $label */
        $label = factory(Label::class)->create();

        $course->labels()->attach($label->id);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($id: ID!, $input: UpdateCourse!) {
                updateCourse(id: $id, input: $input) {
                    id
                    labels {
                        id
                    }
                }
            } 
            GRAPHQL,
            [
                'id'    => $course->id,
                'input' => [
                    'labels' => [
                        'connect' => [$label->id],
                    ],
                ],
            ],
        );

        $this->assertResponseOk();
        $this->assertErrorCategory('database');
    }
}

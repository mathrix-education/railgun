<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use Railgun\Models\Course;
use Railgun\Models\Label;
use function factory;

/**
 * @testdox Mutation updateCourse
 */
class UpdateCourseIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox connects a label to an existing course.
     */
    public function testConnectCourse(): void
    {
        $this->actingAsNewUser(['scopes' => ['labels:read', 'courses:update']]);
        /** @var Course $course */
        $course = factory(Course::class)->create();
        /** @var Label $label */
        $label = factory(Label::class)->create();

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($id: ID!, $input: UpdateCourse!) {
                updateCourse(id: $id, input: $input) {
                    id
                    labels {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            [
                'id' => $course->id,
                'input' => [
                    'labels' => ['connect' => [$label->id]],
                ],
            ],
        );

        $this->seeInDatabase('labelables', [
            'labelable_id'   => $course->id,
            'labelable_type' => 'course',
            'label_id'       => $label->id,
        ]);
    }
}

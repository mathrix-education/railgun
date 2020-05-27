<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Label;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Query readLabel
 */
class ReadLabelIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox returns the label with a nested course
     */
    public function testReadWithCourse(): void
    {
        $course = Course::random();
        /** @var Label $label */
        $label = factory(Label::class)->create();
        $course->labels()->sync([$label->id]);

        $this->withPermissions('labels:read');

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readLabel(id: $id) {
                    id
                    name
                    color
                    courses {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            ['id' => $label->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($course->id, $this->getData('courses.0.id'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Carbon\Carbon;
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
     * @param mixed[] $input The UpdateCourse input.
     */
    private function executeUpdateCourse(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateCourse!) {
                updateCourse(input: $input) {
                    id
                    labels {
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
     * @testdox connects a label to an existing course
     */
    public function testConnectCourse(): void
    {
        $this->withPermissions(['labels:read', 'courses:write']);
        /** @var Course $course */
        $course = factory(Course::class)->create();
        /** @var Label $label */
        $label = factory(Label::class)->create();

        $this->executeUpdateCourse([
            'id'     => $course->id,
            'labels' => ['add' => [$label->id]],
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('labelables', [
            'labelable_id'   => $course->id,
            'labelable_type' => 'course',
            'label_id'       => $label->id,
        ]);
    }

    /**
     * @testdox forbid update for users without courses:freeze scope
     */
    public function testUpdateFrozenCourse(): void
    {
        $course = factory(Course::class)->create([
            'frozen_at' => Carbon::now()->addMonth(),
            'visible'   => true,
        ]);

        $this->withPermissions(['labels:read', 'courses:write']);

        $this->executeUpdateCourse([
            'id'   => $course->id,
            'name' => 'Hello world',
        ]);

        $this->assertResponseOk();
        $this->assertErrorCode('MISSING_SCOPE');
    }
}

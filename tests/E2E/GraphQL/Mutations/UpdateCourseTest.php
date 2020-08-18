<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Label;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;
use function implode;

class UpdateCourseTest extends GraphQLTestCase
{
    use DatabaseTransactions;

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
     * @param mixed[] $input The UpdateCourse input.
     */
    private function executeUpdateCourse(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateCourse!) {
                updateCourse(input: $input) {
                    id
                    seo_keywords
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
        $this->assertErrorCode('MISSING_PERMISSION');
    }

    /**
     * @ticket #436
     */
    public function testUpdateSeoKeywords(): void
    {
        $this->withPermissions(['labels:read', 'courses:write', 'courses:write:seo_keywords']);

        /** @var Course $course */
        $course   = factory(Course::class)->create([
            'visible'      => true,
            'seo_keywords' => null,
        ]);
        $keywords = implode(',', $this->faker->words(3));

        $this->seeInDatabase('courses', ['id' => $course->id, 'seo_keywords' => null]);

        $this->executeUpdateCourse([
            'id'           => $course->id,
            'seo_keywords' => $keywords,
        ]);

        $this->assertEquals($keywords, $this->getData('seo_keywords'));
        $this->seeInDatabase('courses', ['id' => $course->id, 'seo_keywords' => $keywords]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Label;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function collect;
use function factory;

/**
 * @testdox Mutation createCourse
 */
class CreateCourseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox create a new course with attached labels
     */
    public function testCreateWithLabels(): void
    {
        $expectedLabelsCount = 2;

        $payload = Arr::only(factory(Course::class)->raw(), [
            'name',
            'slug',
            'content',
            'premium',
            'visible',
            'notion_id',
        ]);

        $labelIds = factory(Label::class, $expectedLabelsCount)->create()->pluck('id')->toArray();

        $payload['labels'] = ['add' => $labelIds];

        $this->withPermissions('*');
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateCourse!) {
                createCourse(input: $input) {
                    id
                    labels {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            ['input' => $payload],
        );

        $actualLabelIds = collect($this->getData('createCourse.labels'))->map(static fn($l) => $l['id'])->toArray();

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEqualsCanonicalizing($labelIds, $actualLabelIds);
        $this->assertEquals(
            $expectedLabelsCount,
            DB::table('labelables')
                ->where([
                    ['labelable_type', '=', 'COURSE'],
                    ['labelable_id', '=', $this->getData('createCourse.id')],
                ])
                ->whereNotNull('labeled_at')
                ->count(),
        );
    }
}

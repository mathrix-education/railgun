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
 * @testdox [E2E] Mutation createCourse
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

        $payload['labels'] = ['connect' => $labelIds];

        $this->actingAsNewUser('*');
        $this->graphQL(
            <<<'GRAPHQL'
            mutation(
                $name: String!
                $slug: String!
                $content: Silicate!
                $premium: Boolean!
                $visible: Boolean!
                $notion_id: ID!
                $labels: ManageLabels
            ) {
                createCourse(
                    name: $name
                    slug: $slug
                    content: $content
                    premium: $premium
                    visible: $visible
                    notion_id: $notion_id
                    labels: $labels
                ) {
                    id
                    labels {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            $payload,
        );

        $actualLabelIds = collect($this->getData('createCourse.labels'))->map(static fn($l) => $l->id)->toArray();

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

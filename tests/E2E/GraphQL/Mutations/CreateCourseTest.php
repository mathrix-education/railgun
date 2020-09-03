<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Label;
use Railgun\Models\Notion;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function collect;
use function factory;

class CreateCourseTest extends GraphQLTestCase
{
    use DatabaseTransactions;

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

    public function testCreateCloned(): void
    {
        /** @var Course $ref */
        $ref = Course::query()
            ->with('notion.chapter')
            ->whereHas('notion', static function (Builder $query): void {
                $query->where('notions.type', '=', Notion::TYPE_STANDARD);
            })
            ->inRandomOrder()
            ->firstOrFail();

        ClonesGenerator::cloneChapter($ref->notion->chapter);

        $input = Arr::except(
            factory(Course::class)->raw(['notion_id' => $ref->notion_id]),
            ['id', 'created_at', 'updated_at'],
        );

        $this->withPermissions(['*']);
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateCourse!) {
                createCourse(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Course $course */
        $course = Course::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($course->clones->isNotEmpty());
    }
}

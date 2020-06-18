<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Tests\ApplicationTestCase;
use function collect;
use function count;
use function factory;
use function range;

/**
 * @testdox Orderable Model trait
 */
class OrderableTest extends ApplicationTestCase
{
    /**
     * @testdox orders models (notions) during creation
     */
    public function testCreateModels(): void
    {
        $chapterId = DB::table('chapters')->insertGetId(factory(Chapter::class)->raw());
        /** @var Collection|Notion[] $notions */
        $notions = factory(Notion::class, 5)->create([
            'chapter_id' => $chapterId,
            'type' => Notion::TYPE_STANDARD,
        ]);

        // -> 0, 1, 2, 3, 4

        $notions->push(factory(Notion::class)->create([
            'chapter_id' => $chapterId,
            'type'       => Notion::TYPE_STANDARD,
            'order'      => 3,
        ]));

        // -> 0, 1, 2, 4, 5, 3
        //             +  +  ^

        // Test update (swap)
        $notions[3]->refresh();
        $notions[3]->order = 1; // will move 1->2, 2->3
        $notions[3]->save();

        // -> 0, 2, 3, 1, 5, 4
        //       +  +  ^     +

        // Test updating (overflow)
        $notions[2]->refresh();
        $notions[2]->order = 99; // 2->last (5), so 4->4, 5->3
        $notions[2]->save();

        // -> 0, 2, 5, 1, 4, 3
        //          ^     -  -

        // Test detached
        $notions[4]->refresh();
        $notions[4]->chapter_id = Chapter::random()->id;
        $notions[4]->save();
        $notions->splice(4, 1);

        // -> 0, 2, 4, 1, 3
        //          -

        // Test deletion
        $notions[3]->refresh()->delete();
        $notions->splice(3, 1);

        // -> 0, 1, 3, 2

        $ordered  = $notions
            ->map(static fn($n) => $n->refresh()->refresh()->order) // double refresh to stabilize the test
            ->toArray();
        $expected = [0, 1, 3, 2];

        $this->assertEquals($expected, $ordered);
    }

    /**
     * @testdox orders related models (courses and subjects) during creation
     */
    public function testCreateRelatedModels(): void
    {
        /** @var Notion $notion */
        $notion   = factory(Notion::class)->create();
        $courses  = factory(Course::class, 4)->make(['notion_id' => $notion->id, 'order' => null]);
        $subjects = factory(Subject::class, 3)->make(['notion_id' => $notion->id, 'order' => null]);

        $models = [
            $courses[0],
            $subjects[0],
            $courses[1],
            $courses[2],
            $subjects[1],
            $courses[3],
            $subjects[2],
        ];

        $ordered = collect($models)
            ->each(static fn(Model $model) => $model->save())
            ->map(static fn(Model $model) => $model->refresh()->getAttribute('order'))
            ->toArray();

        $this->assertEqualsCanonicalizing(range(0, count($ordered) - 1), $ordered);
    }
}

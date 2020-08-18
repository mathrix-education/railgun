<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use function factory;

/** @coversDefaultClass \Railgun\Models\Subject
 */
class SubjectTest extends ModelTestCase
{
    use DatabaseTransactions;

    /** @covers ::booting
     */
    public function testExercisesPremiumSync(): void
    {
        /** @var Subject $subject */
        $subject = factory(Subject::class)->create(['premium' => false]);
        /** @var Collection|Exercise[] $exercises */
        $exercises = factory(Exercise::class, 5)->create(['subject_id' => $subject->id]);
        $exercises->each(function (Exercise $e): void {
            $e->refresh();
            $this->assertFalse($e->premium);
        });

        $subject->premium = true;
        $subject->save();

        $exercises->each(function (Exercise $e): void {
            $e->refresh();
            $this->assertTrue($e->premium);
        });
    }

    /** @covers ::booting
     */
    public function testExercisesProtectedSync(): void
    {
        /** @var Subject $subject */
        $subject = factory(Subject::class)->create(['protected' => false]);
        /** @var Collection|Exercise[] $exercises */
        $exercises = factory(Exercise::class, 5)->create(['subject_id' => $subject->id]);
        $exercises->each(function (Exercise $e): void {
            $e->refresh();
            $this->assertFalse($e->protected);
        });

        $subject->protected = true;
        $subject->save();

        $exercises->each(function (Exercise $e): void {
            $e->refresh();
            $this->assertTrue($e->protected);
        });
    }

    /**
     * @covers ::notion
     */
    public function testNotion(): void
    {
        $this->assertBelongsTo('notion');
    }

    /**
     * @covers ::exercises
     */
    public function testExercises(): void
    {
        $this->assertHasMany('exercises');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Subject $instance */
        $instance = factory(Subject::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

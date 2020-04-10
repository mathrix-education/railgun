<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Database\Eloquent\Collection;
use Railgun\Models\Exercise;
use Railgun\Models\Subject;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @testdox Model Subjects
 */
class SubjectTest extends ApplicationTestCase
{
    /**
     * @testdox syncs has its premium attribute sync with its exercises.
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
}

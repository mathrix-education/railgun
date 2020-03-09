<?php

declare(strict_types=1);

namespace Tests\MigrationV7;

use Railgun\Models\Exercise;
use Tests\BaseTestCase;
use function count;

class ExercisesTest extends BaseTestCase
{
    public function getExercises(): array
    {
        $this->refreshApplication();

        return Exercise::all()->mapWithKeys(fn($e) => [$e->id => [$e]])->toArray();
    }

    /**
     * @dataProvider getExercises
     *
     * @param Exercise $exercise
     */
    public function testQuestionsCoherence(Exercise $exercise): void
    {
        $count = count($exercise->questions);

        $this->assertCount($count, $exercise->choices, "Exercise {$exercise->id}: choices size mismatch");
        $this->assertCount($count, $exercise->solutions, "Exercise {$exercise->id}: solutions size mismatch");
        $this->assertCount($count, $exercise->explanations, "Exercise {$exercise->id}: explanations size mismatch");
    }
}

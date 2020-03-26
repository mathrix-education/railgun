<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Note;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function class_basename;
use function collect;
use function factory;
use function strtolower;

/**
 * @testdox [Unit] Model Note
 * @coversDefaultClass \Railgun\Models\Note
 */
class NoteTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function createTestNotableDataset(): array
    {
        return collect([
            Course::class,
            Exercise::class,
            Flashcard::class,
            Exam::class,
            Course::class,
        ])
            ->mapWithKeys(fn($class) => [class_basename($class) => [$class]])
            ->toArray();
    }

    /**
     * @dataProvider createTestNotableDataset
     * @testdox      has a valid notable morph (using $class)
     * @covers ::notable
     *
     * @param string $class
     */
    public function testNotable(string $class): void
    {
        /** @var Course|Exercise|Flashcard|Exam $model */
        $model = $class::random();
        $user  = User::random();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id'      => $user->id,
            'notable_type' => strtolower(class_basename($class)),
            'notable_id'   => $model->id,
        ]);

        $note->refresh();
        $this->assertEquals($model->id, $note->notable->id);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Discipline;
use function factory;

/**
 * @testdox Model Discipline
 * @coversDefaultClass \Railgun\Models\Discipline
 */
class DisciplineTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::grades
     */
    public function testGrades(): void
    {
        $this->assertBelongsToMany(
            Discipline::class,
            'grades',
            [],
            [],
            Arr::except(factory(Chapter::class)->raw(), ['grade_id', 'discipline_id']),
        );
    }

    /**
     * @covers ::chapters
     */
    public function testChapters(): void
    {
        $this->assertHasMany(Discipline::class, 'chapters');
    }

    /**
     * @covers ::sessions
     */
    public function testSession(): void
    {
        $this->assertHasMany(Discipline::class, 'sessions');
    }
}

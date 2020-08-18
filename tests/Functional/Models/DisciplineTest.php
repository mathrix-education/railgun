<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Theme;
use function factory;

/**
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
            'grades',
            [],
            [],
            Arr::except(factory(Theme::class)->raw(), ['grade_id', 'discipline_id']),
        );
    }

    /**
     * @covers ::themes
     */
    public function testThemes(): void
    {
        $this->assertHasMany('themes');
    }

    /**
     * @covers ::sessions
     */
    public function testSession(): void
    {
        $this->assertHasMany('sessions');
    }
}

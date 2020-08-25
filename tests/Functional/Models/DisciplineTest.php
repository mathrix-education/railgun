<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

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
            ['order' => 99, 'visible' => true],
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

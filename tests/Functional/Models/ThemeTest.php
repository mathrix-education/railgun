<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \Railgun\Models\Theme
 */
class ThemeTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::discipline
     */
    public function testDiscipline(): void
    {
        $this->assertBelongsTo('discipline');
    }

    /**
     * @covers ::grade
     */
    public function testGrade(): void
    {
        $this->assertBelongsTo('grade');
    }

    /**
     * @covers ::chapters
     */
    public function testChapters(): void
    {
        $this->assertHasMany('chapters');
    }
}

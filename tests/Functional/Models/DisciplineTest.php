<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Discipline;

/**
 * @testdox Model Discipline
 * @coversDefaultClass \Railgun\Models\Discipline
 */
class DisciplineTest extends ModelTestCase
{
    use DatabaseTransactions;

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

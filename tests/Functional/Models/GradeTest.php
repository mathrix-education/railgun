<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Grade;

/**
 * @testdox Model Grade
 * @coversDefaultClass \Railgun\Models\Grade
 */
class GradeTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::parent
     */
    public function testParent(): void
    {
        $this->assertBelongsTo(Grade::class, 'parent');
    }

    /**
     * @covers ::children
     */
    public function testChildren(): void
    {
        $this->assertHasMany(Grade::class, 'children');
    }

    /**
     * @covers ::country
     */
    public function testCountry(): void
    {
        $this->assertBelongsTo(Grade::class, 'country');
    }

    /**
     * @covers ::chapters
     */
    public function testChapters(): void
    {
        $this->assertHasMany(Grade::class, 'chapters');
    }

    /**
     * @covers ::users
     */
    public function testUsers(): void
    {
        $this->assertHasMany(Grade::class, 'users');
    }
}

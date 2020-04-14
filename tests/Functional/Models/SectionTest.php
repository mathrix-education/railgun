<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Section;

/**
 * @testdox Model Section
 * @coversDefaultClass \Railgun\Models\Section
 */
class SectionTest extends ModelTestCase
{
    /**
     * @covers ::training
     */
    public function testTraining(): void
    {
        $this->assertBelongsTo(Section::class, 'training');
    }

    /**
     * @covers ::lessons
     */
    public function testLessons(): void
    {
        $this->assertHasMany(Section::class, 'lessons');
    }
}

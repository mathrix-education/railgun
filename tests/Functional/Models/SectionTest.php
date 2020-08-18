<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

/**
 * @coversDefaultClass \Railgun\Models\Section
 */
class SectionTest extends ModelTestCase
{
    /**
     * @covers ::training
     */
    public function testTraining(): void
    {
        $this->assertBelongsTo('training');
    }

    /**
     * @covers ::lessons
     */
    public function testLessons(): void
    {
        $this->assertHasMany('lessons');
    }
}

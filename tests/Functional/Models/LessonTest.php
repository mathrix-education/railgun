<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Lesson;

/**
 * @testdox Model Lesson
 * @coversDefaultClass \Railgun\Models\Lesson
 */
class LessonTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::section
     */
    public function testSection(): void
    {
        $this->assertBelongsTo(Lesson::class, 'section');
    }
}

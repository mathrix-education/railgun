<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use function factory;

/**
 * @testdox Model Course
 * @coversDefaultClass \Railgun\Models\Course
 */
class CourseTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::notion
     */
    public function testNotion(): void
    {
        $this->assertBelongsTo(Course::class, 'notion');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Course $instance */
        $instance = factory(Course::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

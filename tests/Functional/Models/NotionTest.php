<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Railgun\Models\Notion;
use function factory;

/**
 * @testdox Model Notion
 * @coversDefaultClass \Railgun\Models\Notion
 */
class NotionTest extends ModelTestCase
{
    /**
     * @covers ::chapter
     */
    public function testChapter(): void
    {
        $this->assertBelongsTo(Notion::class, 'chapter');
    }

    /**
     * @covers ::courses
     */
    public function testCourses(): void
    {
        $this->assertHasMany(Notion::class, 'courses');
    }

    /**
     * @covers ::subjects
     */
    public function testSubjects(): void
    {
        $this->assertHasMany(Notion::class, 'subjects');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Notion $instance */
        $instance = factory(Notion::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Notion;
use function factory;

/**
 * @testdox Model Chapter
 * @coversDefaultClass \Railgun\Models\Chapter
 */
class ChapterTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::notions
     */
    public function testNotions(): void
    {
        $this->assertHasMany('notions');
    }

    /**
     * @covers ::summary
     */
    public function testSummary(): void
    {
        $this->assertHasOne('summary', [], ['type' => Notion::TYPE_SUMMARY]);
    }

    /**
     * @covers ::flashcards
     */
    public function testFlashcards(): void
    {
        $this->assertHasMany('flashcards');
    }

    /**
     * @covers ::exams
     */
    public function testExams(): void
    {
        $this->assertHasMany('exams');
    }

    /**
     * @covers ::sessions
     */
    public function testSessions(): void
    {
        $this->assertHasMany('sessions');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Chapter $instance */
        $instance = factory(Chapter::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

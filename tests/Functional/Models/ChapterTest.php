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
     * @covers ::discipline
     */
    public function testDiscipline(): void
    {
        $this->assertBelongsTo(Chapter::class, 'discipline');
    }

    /**
     * @covers ::grade
     */
    public function testGrade(): void
    {
        $this->assertBelongsTo(Chapter::class, 'grade');
    }

    /**
     * @covers ::notions
     */
    public function testNotions(): void
    {
        $this->assertHasMany(Chapter::class, 'notions');
    }

    /**
     * @covers ::summary
     */
    public function testSummary(): void
    {
        $this->assertHasOne(Chapter::class, 'summary', [], ['type' => Notion::TYPE_SUMMARY]);
    }

    /**
     * @covers ::flashcards
     */
    public function testFlashcards(): void
    {
        $this->assertHasMany(Chapter::class, 'flashcards');
    }

    /**
     * @covers ::exams
     */
    public function testExams(): void
    {
        $this->assertHasMany(Chapter::class, 'exams');
    }

    /**
     * @covers ::sessions
     */
    public function testSessions(): void
    {
        $this->assertHasMany(Chapter::class, 'sessions');
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

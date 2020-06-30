<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exam;
use function factory;

/**
 * @testdox Model Exam
 * @coversDefaultClass \Railgun\Models\Exam
 */
class ExamTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::chapter
     */
    public function testChapter(): void
    {
        $this->assertBelongsTo('chapter');
    }

    /**
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Exam $instance */
        $instance = factory(Exam::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Flashcard;
use function factory;

/**
 * @testdox Model Flashcard
 * @coversDefaultClass \Railgun\Models\Flashcard
 */
class FlashcardTest extends ModelTestCase
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
        /** @var Flashcard $instance */
        $instance = factory(Flashcard::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}

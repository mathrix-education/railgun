<?php

declare(strict_types=1);

namespace Tests\MigrationV7;

use JsonException;
use Railgun\Models\Flashcard;
use Tests\BaseTestCase;
use const JSON_THROW_ON_ERROR;
use function count;
use function json_decode;

class FlashcardTest extends BaseTestCase
{
    public function getFlashcards(): array
    {
        $this->refreshApplication();

        return Flashcard::all()->mapWithKeys(fn($f) => [$f->id => [$f]])->toArray();
    }

    /**
     * @dataProvider getFlashcards
     *
     * @param Flashcard $flashcard
     *
     * @throws JsonException
     */
    public function testFlashcardCoherence(Flashcard $flashcard): void
    {
        $fronts = count(json_decode($flashcard->getAttributes()['fronts'], true, 512, JSON_THROW_ON_ERROR));
        $backs  = count(json_decode($flashcard->getAttributes()['backs'], true, 512, JSON_THROW_ON_ERROR));

        $this->assertEquals(
            $fronts,
            $backs,
            "Flashcard {$flashcard->id}: size mismatch, got $fronts fronts and $backs backs."
        );
    }
}

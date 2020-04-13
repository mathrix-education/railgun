<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Railgun\Buster\Pipes\ParseFlashcard;
use Railgun\Buster\Pipes\ReportPipe;
use Railgun\Buster\Pipes\Serialize;
use Railgun\Buster\Pipes\Slug;
use Railgun\Buster\Pipes\Strip;
use Railgun\Buster\Pipes\ToArray;
use Railgun\Buster\Pipes\TrimFlashcard;
use Railgun\Buster\Pipes\Unserialize;
use Railgun\Buster\Pipes\ValidateFlashcard;
use stdClass;

/**
 * Convert flashcard from modules.
 */
class FlashcardPipeline
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @param stdClass $flashcard The input flashcard.
     *
     * @return stdClass|bool The converted flashcard on success, false otherwise.
     */
    public function send(stdClass $flashcard)
    {
        return $this->pipeline->send($flashcard)
            ->through([
                new ToArray(),
                new ReportPipe('flashcard'),
                new Unserialize(['fronts', 'backs']),
                new TrimFlashcard(),
                new ValidateFlashcard(),
                new ParseFlashcard(),
                new Serialize(['fronts', 'backs']),
                new Slug(),
                new Strip([
                    'id',
                    'name',
                    'slug',
                    'fronts',
                    'backs',
                    'order',
                    'premium',
                    'visible',
                    'chapter_id',
                    'created_at',
                    'updated_at',
                    'mapping',
                ]),
            ])->thenReturn();
    }
}

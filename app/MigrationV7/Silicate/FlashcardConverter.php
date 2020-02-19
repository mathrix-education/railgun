<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Silicate;

use JsonException;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function array_filter;
use function is_string;
use function json_decode;

class FlashcardConverter
{
    private Converter $converter;

    private function __construct()
    {
        $this->converter = Converter::new();
    }

    public static function new(): FlashcardConverter
    {
        return new self();
    }

    /**
     * @param stdClass $flashcard
     *
     * @return stdClass
     *
     * @throws JsonException
     */
    public function convert(stdClass $flashcard): stdClass
    {
        $flashcard = $this->parse($flashcard);
        $flashcard = $this->strip($flashcard);

        $out = clone $flashcard;

        foreach ($flashcard->fronts as $k => $front) {
            $out->fronts[$k] = $this->converter->convert($front);
        }

        foreach ($flashcard->backs as $k => $back) {
            $out->backs[$k] = $this->converter->convert($back);
        }

        return $out;
    }

    /**
     * @param stdClass $flashcard
     *
     * @return stdClass
     *
     * @throws JsonException
     */
    private function parse(stdClass $flashcard): stdClass
    {
        if (is_string($flashcard->fronts)) {
            $flashcard->fronts = json_decode($flashcard->fronts, true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_string($flashcard->backs)) {
            $flashcard->backs = json_decode($flashcard->backs, true, 512, JSON_THROW_ON_ERROR);
        }

        return $flashcard;
    }

    private function strip(stdClass $flashcard): stdClass
    {
        $flashcard->fronts = array_filter($flashcard->fronts, fn($q) => !empty($q));
        $flashcard->backs  = array_filter($flashcard->backs, fn($q) => !empty($q));

        return $flashcard;
    }
}

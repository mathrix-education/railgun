<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Railgun\Buster\Silicate\SilicateConverter;
use stdClass;
use function app;

/**
 * Parse a flashcard using the {@see SilicateConverter}.
 */
class ParseFlashcard extends Pipe
{
    private SilicateConverter $converter;

    public function __construct()
    {
        $this->converter = app()->make(SilicateConverter::class);
    }

    /**
     * @param stdClass $flashcard The flashcard to parse.
     * @param Closure  $next      The next pipe.
     *
     * @return stdClass The parsed flashcard.
     */
    public function handle(stdClass $flashcard, Closure $next): stdClass
    {
        foreach ($flashcard->fronts as $k => $front) {
            $flashcard->fronts[$k] = $this->converter->convert($front);
        }

        foreach ($flashcard->backs as $k => $back) {
            $flashcard->backs[$k] = $this->converter->convert($back);
        }

        return $next($flashcard);
    }
}

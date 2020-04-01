<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function array_filter;
use function is_string;

/**
 * Trim the empty "fronts" and "backs" of a flashcard.
 */
class TrimFlashcard extends Pipe
{
    /**
     * @param stdClass $flashcard The flashcard to trim.
     * @param Closure  $next      The next pipe.
     *
     * @return stdClass The trimmed flashcard.
     */
    public function handle(stdClass $flashcard, Closure $next): stdClass
    {
        /**
         * @param string $data The data to check.
         *
         * @return bool If the data is valid.
         */
        $valid = static function ($data) {
            if ($data === null) {
                return false;
            }

            if (is_string($data)) {
                return $data !== '';
            }

            return false;
        };

        $flashcard->fronts = array_filter($flashcard->fronts, $valid);
        $flashcard->backs  = array_filter($flashcard->backs, $valid);

        return $next($flashcard);
    }
}

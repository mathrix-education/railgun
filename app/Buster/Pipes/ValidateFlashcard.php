<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function count;
use function property_exists;

class ValidateFlashcard extends Pipe
{
    /**
     * @param stdClass $flashcard The flashcard to validate.
     * @param Closure  $next      The next pipe.
     *
     * @return stdClass The validated flashcard.
     */
    public function handle(stdClass $flashcard, Closure $next): stdClass
    {
        // Check properties existence
        if (!property_exists($flashcard, 'fronts')) {
            return $this->failed($flashcard, 'Missing "fronts" field');
        }

        if (!property_exists($flashcard, 'backs')) {
            return $this->failed($flashcard, 'Missing "backs" field');
        }

        // Check counts
        $frontsCount = count($flashcard->fronts);
        if ($frontsCount === 0) {
            return $this->failed($flashcard, '"fronts" is an empty array');
        }

        $backsCount = count($flashcard->backs);
        if ($frontsCount !== $backsCount) {
            return $this->failed($flashcard, '"fronts" and "backs" sizes mismatch');
        }

        return $next($flashcard);
    }
}

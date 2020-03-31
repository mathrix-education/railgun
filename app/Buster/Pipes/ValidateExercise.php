<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function count;
use function property_exists;

class ValidateExercise extends Pipe
{
    /**
     * @param stdClass $flashcard The trimmed exercise.
     * @param Closure  $next      The next pipe.
     *
     * @return stdClass The validated exercise.
     */
    public function handle(stdClass $flashcard, Closure $next): stdClass
    {
        // Check properties existence
        if (!property_exists($flashcard, 'questions')) {
            return $this->failed($flashcard, 'Missing "questions" field');
        }

        if (!property_exists($flashcard, 'choices')) {
            return $this->failed($flashcard, 'Missing "choices" field');
        }

        if (!property_exists($flashcard, 'solutions')) {
            return $this->failed($flashcard, 'Missing "solutions" field');
        }

        if (!property_exists($flashcard, 'explanations')) {
            return $this->failed($flashcard, 'Missing "explanations" field');
        }

        // Check counts
        $questionsCount = count($flashcard->questions);
        if ($questionsCount === 0) {
            return $this->failed($flashcard, '"questions" is an empty array');
        }

        $choicesCount = count($flashcard->choices);
        if ($questionsCount !== $choicesCount) {
            return $this->failed($flashcard, '"questions" and "choices" sizes mismatch');
        }

        $solutionsCount = count($flashcard->solutions);
        if ($questionsCount !== $solutionsCount) {
            return $this->failed($flashcard, '"questions" and "solutions" sizes mismatch');
        }

        $explanationsCount = count($flashcard->explanations);
        if ($questionsCount !== $explanationsCount) {
            return $this->failed($flashcard, '"questions" and "explanations" sizes mismatch');
        }

        return $next($flashcard);
    }
}

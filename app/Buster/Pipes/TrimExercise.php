<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function array_filter;
use function array_map;
use function count;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

/**
 * Normalize a exercise.
 */
class TrimExercise extends Pipe
{
    /**
     * @param stdClass $flashcard The exercise.
     * @param Closure  $next      The next pipe.
     *
     * @return mixed
     */
    public function handle(stdClass $flashcard, Closure $next)
    {
        /**
         * @param int|string|mixed[] $data The data to check.
         *
         * @return bool If the data is valid.
         */
        $valid = static function ($data) {
            if ($data === null) {
                return false;
            }

            if (is_array($data)) {
                return count($data) > 0;
            }

            if (is_string($data) && !is_numeric($data)) {
                return $data !== '';
            }

            return is_int($data);
        };

        // Normalize questions
        $flashcard->questions = array_filter($flashcard->questions, $valid);

        // Normalize choices
        $flashcard->choices = array_map(static function ($choices) use ($valid) {
            return array_filter($choices, $valid);
        }, $flashcard->choices);
        $flashcard->choices = array_filter($flashcard->choices, $valid);

        // Normalize solutions
        $flashcard->solutions = array_map(static function ($solutions) use ($valid) {
            return array_filter($solutions, $valid);
        }, $flashcard->solutions);
        $flashcard->solutions = array_filter($flashcard->solutions, $valid);

        // Normalize explanations
        $flashcard->explanations = array_map(static function ($explanations) use ($valid) {
            return array_filter($explanations, $valid);
        }, $flashcard->explanations);
        $flashcard->explanations = array_filter($flashcard->explanations, $valid);

        return $next($flashcard);
    }
}

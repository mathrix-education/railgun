<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Railgun\Buster\Utils\Vector;
use stdClass;
use function array_fill;
use function array_filter;
use function array_slice;
use function count;
use function trim;

/**
 * Normalize exams.
 */
class NormalizeExam extends Pipe
{
    /**
     * @param stdClass $exam The exam to normalize.
     * @param Closure  $next The next pipe.
     *
     * @return stdClass The normalized exam.
     */
    public function handle(stdClass $exam, Closure $next): stdClass
    {
        if (!$this->isNormalizable($exam)) {
            return $this->failed($exam, 'Exam is not normalizable.');
        }

        // Initial fill
        $normalized               = clone $exam;
        $normalized->questions    = [];
        $normalized->points       = [];
        $normalized->explanations = [];

        foreach ($exam->questions as $i => $question) {
            $data = $this->question(
                $question,
                $exam->solutions[$i],
                $exam->corrections[$i],
            );

            if ($data === false) {
                return $this->failed($exam, "Exam question n°$i is not normalizable");
            }

            if ($data === null) {
                // Skip question
                continue;
            }

            [$points, $explanations] = $data;

            if (count($points) > count($explanations)) {
                // Too much points, remove the extra ones
                $points = array_slice($points, 0, count($explanations));
            } elseif (count($points) < count($explanations)) {
                // Missing points, add extra zeros
                $zeros  = array_fill(0, count($explanations) - count($points), 0);
                $points = [...$points, ...$zeros];
            }

            $normalized->questions[]    = $question;
            $normalized->points[]       = $points;
            $normalized->explanations[] = $explanations;
        }

        return $next($normalized);
    }

    /**
     * @param stdClass $exam The exam.
     *
     * @return bool If the exam is normalizable.
     */
    private function isNormalizable(stdClass $exam): bool
    {
        $questionsCount   = count($exam->questions);
        $solutionsCount   = count($exam->solutions);
        $correctionsCount = count($exam->corrections);

        return $solutionsCount === $questionsCount
            && $correctionsCount === $questionsCount
            && $questionsCount !== 0
            && $solutionsCount !== 0
            && $correctionsCount !== 0;
    }

    /**
     * @param string   $question    The question string.
     * @param int[]    $solutions   The question solutions.
     * @param string[] $corrections The question corrections.
     *
     * @return mixed[]|bool|null
     */
    private function question(string $question, array $solutions, array $corrections)
    {
        $filter      = static fn($a) => $a !== null && $a !== '';
        $solutions   = array_filter($solutions, $filter);
        $corrections = array_filter($corrections, $filter);

        if (
            trim($question) === ''
            && (count($solutions) === 0 || Vector::isNull($solutions))
            && (count($corrections) === 0 || Vector::isNull($corrections))
        ) {
            return null; // Empty questions but also empty solution and correction, forgive and skip
        }

        if (trim($question) !== '' && (count($solutions) === 0 || count($corrections) === 0)) {
            // The questions is set but not the points nor the explanations: this is a question statement
            // e.g.: Let f the function blablabla
            return [[], []]; // no associated points nor explanations
        }

        if (Vector::isInt($solutions) && Vector::isInt($corrections)) {
            // Both are points, there is now real explanations => drop the points, they are pointless
            return [[], []]; // no associated points nor explanations
        }

        if (Vector::isInt($solutions) && Vector::isString($corrections)) {
            // solution: int[] and correction: string[]
            return [$solutions, $corrections];
        }

        if (Vector::isString($solutions) && Vector::isInt($corrections)) {
            // solution: string[] and correction: int[]
            return [$corrections, $solutions];
        }

        return false;
    }
}

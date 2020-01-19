<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use RuntimeException;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function array_fill;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function json_decode;
use function preg_match;
use function str_replace;

class ExamConverter
{
    public static function decode(stdClass $exam): stdClass
    {
        $exam->questions   = json_decode($exam->questions ?? '[]', false, 512, JSON_THROW_ON_ERROR);
        $exam->solutions   = json_decode($exam->solutions ?? '[]', false, 512, JSON_THROW_ON_ERROR);
        $exam->corrections = json_decode($exam->corrections ?? '[]', false, 512, JSON_THROW_ON_ERROR);

        return $exam;
    }

    public static function isNormalizable(stdClass $exam): bool
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
     * Normalize solutions and corrections to cleaner structure.
     *
     * @param stdClass $exam
     *
     * @return stdClass
     */
    public static function normalize(stdClass $exam): stdClass
    {
        $questionsCount = count($exam->questions);

        // Initial fill
        $normalized               = clone $exam;
        $normalized->points       = [];
        $normalized->explanations = [];

        for ($i = 0; $i < $questionsCount; $i++) {
            $question   = $exam->questions[$i]; // string
            $solution   = $exam->solutions[$i]; // string[] or int[]
            $correction = $exam->corrections[$i]; // string[] or int[]

            if (empty($question)
                && (empty($solution) || ArrayUtils::isNullArray($solution))
                && (empty($correction) || ArrayUtils::isNullArray($correction))
            ) {
                // Empty questions but also empty solution and correction, just skip
                continue;
            }

            if (!empty($question) && (empty($solution) || empty($correction))) {
                // The questions is set but not the points nor the explanations: this is a neutral setup question
                // e.g.: Let f the function blablabla
                $normalized->points[]       = []; // no associated points
                $normalized->explanations[] = []; // no associated explanations
                continue;
            }

            if (ArrayUtils::isNullArray($solution)) {
                // Malformed due to array of null solutions
                return ExamUtils::malformed($exam, "solutions[$i] is an array of null values");
            }
            if (ArrayUtils::isNullArray($correction)) {
                // Malformed due to array of null correction
                return ExamUtils::malformed($exam, "corrections[$i] is an array of null values");
            }

            if (ArrayUtils::isIntegerArray($solution) && ArrayUtils::isIntegerArray($correction)) {
                // Both are points, there is now real explanations => drop the points, they are pointless
                $normalized->points[]       = [];
                $normalized->explanations[] = [];
                continue;
            }

            if (ArrayUtils::isIntegerArray($solution) && ArrayUtils::isStringArray($correction)) {
                // solution: int[] and correction: string[]
                $normalized->points[]       = $solution;
                $normalized->explanations[] = $correction;
                continue;
            }
            if (ArrayUtils::isStringArray($solution) && ArrayUtils::isIntegerArray($correction)) {
                // solution: string[] and correction: int[]
                $normalized->points[]       = $correction;
                $normalized->explanations[] = $solution;
                continue;
            }

            throw new RuntimeException("Error while normalizing $exam->id (iteration $i)");
        }

        $normalizedCount = count($normalized->points);
        for ($i = 0; $i < $normalizedCount; $i++) {
            $countP = count($normalized->points[$i]);
            $countE = count($normalized->explanations[$i]);

            if ($countP === $countE) {
                // Everything is good
                continue;
            }

            if ($countP > $countE) {
                // Too much points, remove the extra ones
                $normalized->points[$i] = array_slice($normalized->points[$i], 0, $countE);
            } else {
                // Missing points, add extra zeros
                $zeros                  = array_fill(0, $countE - $countP, 0);
                $normalized->points[$i] = [...$normalized->points[$i], ...$zeros];
            }
        }

        return $normalized;
    }

    /**
     * After normalization, merge questions, points and explanations into a single Silicate wall.
     *
     * @param stdClass $exam
     *
     * @return stdClass
     */
    public static function reshape(stdClass $exam): stdClass
    {
        $wall = [];

        // Extract duration
        preg_match('/(\d+)--/', $exam->content, $matches);
        $exam->duration = isset($matches[1]) ? (int)$matches[1] : null;

        if ($exam->duration !== null) {
            $exam->content = StringUtils::mtrim(str_replace("{$exam->duration}--", '', $exam->content));
        }

        // Extract content
        $exam->content = str_replace('<BR/>', '', $exam->content);
        $exam->content = str_replace("\n", '', $exam->content);
        $exam->content = str_replace("<span class='v2_test_enonce'></span>", '', $exam->content);

        $wall = [...$wall, ...MathParser::toWall($exam->content)];

        // Extract questions
        $questionsCount    = count($exam->points);
        $questionsIterator = -1;
        $questions         = [];

        for ($i = 0; $i < $questionsCount; $i++) {
            $questionText = StringUtils::mtrim($exam->questions[$i]);
            preg_match('/^([\d]+)-\s+([\s\S]*)/m', $questionText, $matches1);
            preg_match('/^([a-z]+)--\s+([\s\S]*)/m', $questionText, $matches2);

            if (!empty($matches1) || empty($matches2)) {
                // Level 1
                $questions[] = BrickFactory::question(
                    MathParser::toWall($matches1[2] ?? $questionText),
                    array_map(fn($e) => MathParser::toWall($e), $exam->explanations[$i]),
                    $exam->points[$i]
                );
                $questionsIterator++;
            } else {
                // Level 2
                $questions[$questionsIterator]['children'][] = BrickFactory::question(
                    MathParser::toWall($matches2[2]),
                    array_map(fn($e) => MathParser::toWall($e), $exam->explanations[$i]),
                    $exam->points[$i],
                    false
                );
            }
        }

        $wall = [...$wall, ...$questions];

        $exam->content = array_values($wall);

        return $exam;
    }

    /**
     * Remove unused exam fields.
     *
     * @param stdClass $exam
     *
     * @return stdClass
     */
    public static function strip(stdClass $exam): stdClass
    {
        unset(
            $exam->type,
            $exam->choices,
            $exam->questions,
            $exam->points,
            $exam->explanations,
            $exam->solutions,
            $exam->corrections,
            $exam->options,
            $exam->seo_index,
            $exam->seo_title,
            $exam->seo_description,
            $exam->seo_slug,
            $exam->seo_keywords
        );

        return $exam;
    }
}

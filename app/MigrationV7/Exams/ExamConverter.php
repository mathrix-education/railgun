<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use stdClass;
use function array_fill;
use function array_map;
use function array_merge;
use function count;
use function json_decode;
use function preg_match;
use function str_replace;
use const JSON_THROW_ON_ERROR;

class ExamConverter
{
    private stdClass $raw;
    private stdClass $work;
    private int      $duration;

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

        return $solutionsCount === $questionsCount && $correctionsCount === $questionsCount;
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
        // Initial fill
        $normalized = clone $exam;

        if (ExamUtils::isIntegerMatrix($exam->solutions)) {
            $normalized->points = $exam->solutions;
        } elseif (ExamUtils::isIntegerMatrix($exam->corrections)) {
            $normalized->points = $exam->corrections;
        }

        if (ExamUtils::isStringMatrix($exam->solutions)) {
            $normalized->explanations = $exam->solutions;
        } elseif (ExamUtils::isStringMatrix($exam->corrections)) {
            $normalized->explanations = $exam->corrections;
        }

        if (!isset($normalized->points, $normalized->explanations)) {
            $points       = [];
            $explanations = [];

            // We need a more precise analysis
            foreach ($exam->questions as $i => $q) {
                $solution   = $exam->solutions[$i];
                $correction = $exam->corrections[$i];

                if (count($solution) === 0 && count($correction) === 0) {
                    // Sometimes, $solution = [] and $correction = [], skip the "question"
                    continue;
                }

                if (count($solution) === 0 && ExamUtils::isIntegerArray($correction)) {
                    continue;
                }

                if (count($correction) === 0 && ExamUtils::isIntegerArray($solution)) {
                    continue;
                }

                if (ExamUtils::isNullArray($solution) && ExamUtils::isStringArray($correction)) {
                    $solution = array_fill(0, count($correction), 0);
                }

                if (ExamUtils::hasNullValues($solution) && ExamUtils::isStringArray($correction)) {
                    foreach ($solution as $k => $v) {
                        $solution[$k] ??= 0;
                    }
                }

                if (ExamUtils::isIntegerArray($solution) && ExamUtils::isStringArray($correction)) {
                    $points[]       = $solution;
                    $explanations[] = $correction;
                } elseif (ExamUtils::isStringArray($solution) && ExamUtils::isIntegerArray($correction)) {
                    $points[]       = $correction;
                    $explanations[] = $solution;
                } elseif (ExamUtils::isIntegerArray($solution) && ExamUtils::isIntegerArray($correction)) {
                    // We got two arrays of integer, we assume that the "solution" one is the points
                    $points[]       = $solution;
                    $explanations[] = ''; // Empty string as explanation
                } else {
                    $exam->malformed = true;

                    return $exam;
                    //throw new RuntimeException("Invalid normalization of exam id {$exam->id}. Got:\n"
                    //    . json_encode($exam, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512));
                }
            }

            if (count($exam->questions) !== count($points) || count($exam->questions) !== count($explanations)) {
                $exam->malformed = true;

                return $exam;
            }

            $normalized->points       = $points;
            $normalized->explanations = $explanations;
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

        $exam->content_old = $exam->content;

        // Extract duration
        preg_match('/(\d+)--/', $exam->content, $matches);
        $exam->duration = isset($matches[1]) ? (int)$matches[1] : null;

        if ($exam->duration !== null) {
            $exam->content = StringUtils::mtrim(str_replace("{$exam->duration}--", '', $exam->content));
        }

        // Extract content
        $exam->content = str_replace('<BR/>', '(B.R)', $exam->content);
        $exam->content = str_replace("\n", '(N.N)', $exam->content);
        $exam->content = str_replace("<span class='v2_test_enonce'></span>", '', $exam->content);

        $wall = array_merge($wall, MathParser::toWall($exam->content));

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
                $questions[$questionsIterator][] = BrickFactory::question(
                    MathParser::toWall($matches2[2]),
                    array_map(fn($e) => MathParser::toWall($e), $exam->explanations[$i]),
                    $exam->points[$i],
                    false
                );
            }
        }

        $wall = [...$wall, ...$questions];

        $exam->content = $wall;

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
            $exam->content_old,
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

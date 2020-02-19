<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Silicate;

use JsonException;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function array_filter;
use function array_map;
use function is_string;
use function json_decode;

class ExerciseConverter
{
    private Converter $converter;

    private function __construct()
    {
        $this->converter = Converter::new();
    }

    public static function new(): ExerciseConverter
    {
        return new self();
    }

    /**
     * @param stdClass $exercise
     *
     * @return stdClass
     *
     * @throws JsonException
     */
    public function convert(stdClass $exercise): stdClass
    {
        $exercise = $this->parse($exercise);
        $exercise = $this->strip($exercise);

        $out = clone $exercise;

        foreach ($exercise->questions as $k => $question) {
            $out->questions[$k] = $this->converter->convert($question);
        }

        foreach ($exercise->choices as $k => $choicesGroup) {
            foreach ($choicesGroup as $j => $choice) {
                $out->choices[$k][$j] = $this->converter->convert($choice);
            }
        }

        foreach ($exercise->explanations as $k => $explanationGroup) {
            foreach ($explanationGroup as $j => $explanation) {
                $out->explanations[$k][$j] = $this->converter->convert($explanation);
            }
        }

        return $out;
    }

    /**
     * @param stdClass $exercise
     *
     * @return stdClass
     *
     * @throws JsonException
     */
    private function parse(stdClass $exercise): stdClass
    {
        if (is_string($exercise->questions)) {
            $exercise->questions = json_decode($exercise->questions, true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_string($exercise->choices)) {
            $exercise->choices = json_decode($exercise->choices, true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_string($exercise->explanations)) {
            $exercise->explanations = json_decode($exercise->explanations, true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_string($exercise->solutions)) {
            $exercise->solutions = json_decode($exercise->solutions, true, 512, JSON_THROW_ON_ERROR);
        }

        return $exercise;
    }

    private function strip(stdClass $exercise): stdClass
    {
        $exercise->questions = array_filter($exercise->questions, fn($q) => !empty($q));

        $exercise->choices = array_map(static function ($choices) {
            return array_filter($choices, fn($c) => !empty($c));
        }, $exercise->choices);
        $exercise->choices = array_filter($exercise->choices, fn($q) => !empty($q));

        $exercise->solutions = array_map(static function ($solutions) {
            return array_filter($solutions, fn($s) => !empty($s));
        }, $exercise->solutions);
        $exercise->solutions = array_filter($exercise->solutions, fn($q) => !empty($q));

        $exercise->explanations = array_map(static function ($explanations) {
            return array_filter($explanations, fn($e) => !empty($e));
        }, $exercise->explanations);
        $exercise->explanations = array_filter($exercise->explanations, fn($q) => !empty($q));

        return $exercise;
    }
}

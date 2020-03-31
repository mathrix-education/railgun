<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Railgun\Buster\Silicate\SilicateConverter;
use stdClass;
use function app;

/**
 * Parse an exercise using the {@see SilicateConverter}.
 */
class ParseExercise extends Pipe
{
    private SilicateConverter $converter;

    public function __construct()
    {
        $this->converter = app()->make(SilicateConverter::class);
    }

    /**
     * @param stdClass $exercise The exercise to parse.
     * @param Closure  $next     The next pipe.
     *
     * @return mixed
     */
    public function handle(stdClass $exercise, Closure $next)
    {
        // Convert questions
        foreach ($exercise->questions as $k => $question) {
            $exercise->questions[$k] = $this->converter->convert($question);
        }

        // Convert choices
        foreach ($exercise->choices as $k => $choicesGroup) {
            foreach ($choicesGroup as $j => $choice) {
                $exercise->choices[$k][$j] = $this->converter->convert($choice);
            }
        }

        // Convert explanations
        foreach ($exercise->explanations as $k => $explanationGroup) {
            foreach ($explanationGroup as $j => $explanation) {
                $exercise->explanations[$k][$j] = $this->converter->convert($explanation);
            }
        }

        return $next($exercise);
    }
}

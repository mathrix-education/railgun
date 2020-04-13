<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Railgun\Buster\Pipes\ParseExercise;
use Railgun\Buster\Pipes\ReportPipe;
use Railgun\Buster\Pipes\Serialize;
use Railgun\Buster\Pipes\Strip;
use Railgun\Buster\Pipes\ToArray;
use Railgun\Buster\Pipes\TrimExercise;
use Railgun\Buster\Pipes\Unserialize;
use Railgun\Buster\Pipes\ValidateExercise;
use stdClass;

/**
 * Convert exercise from modules.
 */
class ExercisePipeline
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @param stdClass $exercise The input exercise.
     *
     * @return stdClass|bool The converted exercise on success, false otherwise.
     */
    public function send(stdClass $exercise)
    {
        return $this->pipeline->send($exercise)
            ->through([
                new ToArray(),
                new ReportPipe('exercise'),
                new Unserialize(['questions', 'choices', 'solutions', 'explanations']),
                new TrimExercise(),
                new ValidateExercise(),
                new ParseExercise(),
                new Serialize(['questions', 'choices', 'solutions', 'explanations']),
                new Strip([
                    'id',
                    'type',
                    'questions',
                    'choices',
                    'solutions',
                    'explanations',
                    'order',
                    'premium',
                    'visible',
                    'subject_id',
                    'created_at',
                    'updated_at',
                    'mapping',
                ]),
            ])->thenReturn();
    }
}

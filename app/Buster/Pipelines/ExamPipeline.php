<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Railgun\Buster\Pipes\ExtractExamDuration;
use Railgun\Buster\Pipes\NormalizeExam;
use Railgun\Buster\Pipes\ParseExam;
use Railgun\Buster\Pipes\ReportPipe;
use Railgun\Buster\Pipes\RestructureExamQuestions;
use Railgun\Buster\Pipes\Serialize;
use Railgun\Buster\Pipes\Slug;
use Railgun\Buster\Pipes\Strip;
use Railgun\Buster\Pipes\ToArray;
use Railgun\Buster\Pipes\Unserialize;
use stdClass;

/**
 * Convert an exam from modules.
 */
class ExamPipeline
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @param stdClass $exam The original exam.
     *
     * @return stdClass|bool The converted exam on success, false otherwise.
     */
    public function send(stdClass $exam)
    {
        return $this->pipeline
            ->send($exam)
            ->through([
                new ToArray(),
                new ReportPipe('exam'),
                new Unserialize(['questions', 'solutions', 'corrections']),
                new NormalizeExam(),
                new RestructureExamQuestions(),
                new ExtractExamDuration(),
                new ParseExam(),
                new Serialize(['content']),
                new Slug(),
                new Strip([
                    'name', 'slug', 'duration', 'content', 'order', 'premium', 'visible', 'chapter_id',
                    'created_at', 'updated_at', 'mapping',
                ]),
            ])
            ->thenReturn();
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Railgun\Buster\Pipes\ReportPipe;
use Railgun\Buster\Pipes\Slug;
use Railgun\Buster\Pipes\ToArray;
use Railgun\Buster\Pipes\ValidateCourse;
use stdClass;

class CoursePipeline
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @param stdClass $course The input course.
     *
     * @return stdClass|bool The converted course on success, false otherwise.
     */
    public function send(stdClass $course)
    {
        return $this->pipeline->send($course)
            ->through([
                new ToArray(),
                new ReportPipe('exercise'),
                new ValidateCourse(),
                new Slug(),
            ])
            ->thenReturn();
    }
}

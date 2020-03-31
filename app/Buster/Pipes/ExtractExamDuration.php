<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function preg_match;
use function trim;

/**
 * Extract duration from the exam content field.
 */
class ExtractExamDuration extends Pipe
{
    /**
     * @param stdClass $exam The exam.
     * @param Closure  $next The next pipe.
     *
     * @return stdClass The exam with the "duration" field.
     */
    public function handle(stdClass $exam, Closure $next): stdClass
    {
        if (preg_match('/^([\d]+)--\s+([\s\S]*)/m', $exam->content, $matches)) {
            $exam->duration = (int)$matches[1];
            $exam->content  = trim($matches[2]);
        } else {
            $exam->duration = null;
        }

        return $next($exam);
    }
}

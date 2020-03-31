<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function property_exists;

class ValidateCourse extends Pipe
{
    /**
     * @param stdClass $course The course to validate.
     * @param Closure  $next   The next pipe.
     */
    public function handle(stdClass $course, Closure $next): stdClass
    {
        if (!property_exists($course, 'content') || $course->content === '' || $course->content === null) {
            return $next($this->failed($course, 'Missing content field'));
        }

        return $next($course);
    }
}

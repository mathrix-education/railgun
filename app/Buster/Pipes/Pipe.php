<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function debug_backtrace;

abstract class Pipe
{
    /**
     * @param stdClass $model The model.
     * @param Closure  $next  The next pipe.
     *
     * @return stdClass|bool
     */
    abstract public function handle(stdClass $model, Closure $next);

    /**
     * Mark a model as invalid and set the "failed" property from the {@see ReportPipe}.
     *
     * @param stdClass $model   The model.
     * @param string   $message The failure message.
     *
     * @return stdClass The model with the "failed" property.
     */
    protected function failed(stdClass $model, string $message): stdClass
    {
        $trace                 = debug_backtrace()[0];
        $model->debug          = new stdClass();
        $model->debug->message = $message;
        $model->debug->trace   = "{$trace['file']}({$trace['line']}";

        return $model;
    }
}

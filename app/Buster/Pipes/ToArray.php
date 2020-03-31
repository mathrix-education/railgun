<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;

class ToArray extends Pipe
{
    /**
     * @param stdClass $model The model as a stdClass.
     * @param Closure  $next  The next pipe.
     *
     * @return mixed[]|bool The model as an array.
     */
    public function handle(stdClass $model, Closure $next)
    {
        $out = $next($model);

        return $out instanceof stdClass ? (array)$out : $out;
    }
}

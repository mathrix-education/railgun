<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Illuminate\Support\Str;
use stdClass;
use function property_exists;

class Slug extends Pipe
{
    private string $from;
    private string $to;

    public function __construct(string $from = 'name', string $to = 'slug')
    {
        $this->from = $from;
        $this->to   = $to;
    }

    /**
     * @param stdClass $model The model to slug.
     * @param Closure  $next  The next pipe.
     *
     * @return stdClass The sluggified model.
     */
    public function handle(stdClass $model, Closure $next): stdClass
    {
        if (!property_exists($model, $this->from)) {
            return $this->failed($model, "Missing \"{$this->from}\" property");
        }

        $model->{$this->to} = Str::slug($model->{$this->from});

        return $next($model);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function property_exists;

class Strip extends Pipe
{
    /** @var string[] The JSON fields. */
    private array $fields;
    private bool  $whitelist;

    /**
     * @param string[] $fields    The to keep/delete.
     * @param bool     $whitelist If the fields should be kep (whitelist = true) or deleted (false).
     */
    public function __construct(array $fields = [], bool $whitelist = true)
    {
        $this->fields    = $fields;
        $this->whitelist = $whitelist;
    }

    /**
     * @param stdClass $model The model.
     * @param Closure  $next  The next pipe.
     *
     * @return mixed
     */
    public function handle(stdClass $model, Closure $next)
    {
        unset($model->failed, $model->trace);

        if ($this->whitelist) {
            $clean = new stdClass();

            foreach ($this->fields as $field) {
                if (!property_exists($model, $field)) {
                    continue;
                }
                $clean->{$field} = $model->{$field};
            }

            return $next($clean);
        }

        foreach ($this->fields as $field) {
            unset($model->{$field});
        }

        return $next($model);
    }
}

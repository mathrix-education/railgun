<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use JsonException;
use stdClass;
use function json_encode;
use function property_exists;
use const JSON_THROW_ON_ERROR;

class Serialize extends Pipe
{
    /** @var string[] The JSON fields. */
    private array $fields;

    /**
     * @param string[] $fields The JSON fields.
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param stdClass $model The model to serialize.
     * @param Closure  $next  The next pipe.
     *
     * @return stdClass The serialized model.
     */
    public function handle(stdClass $model, Closure $next): stdClass
    {
        foreach ($this->fields as $field) {
            if (!property_exists($model, $field)) {
                return $this->failed($model, 'Missing "' . $field . '" field');
            }

            try {
                $model->$field = json_encode($model->$field, JSON_THROW_ON_ERROR, 512);
            } catch (JsonException $exception) {
                return $this->failed($model, 'Unable to decode the "' . $field . '" field');
            }
        }

        return $next($model);
    }
}

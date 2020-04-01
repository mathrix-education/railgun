<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use JsonException;
use stdClass;
use function is_string;
use function json_decode;
use function property_exists;
use const JSON_THROW_ON_ERROR;

/**
 * Automatically unserialize the corresponding JSON fields.
 */
class Unserialize extends Pipe
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
     * @param stdClass $model The model to unserialize.
     * @param Closure  $next  The next pipe.
     *
     * @return stdClass The unserialized model.
     */
    public function handle(stdClass $model, Closure $next): stdClass
    {
        foreach ($this->fields as $field) {
            if (!property_exists($model, $field) || !is_string($model->$field)) {
                return $this->failed($model, 'Missing "' . $field . '" field');
            }

            try {
                $model->$field = json_decode($model->$field, false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $this->failed($model, 'Unable to decode the "' . $field . '" field');
            }
        }

        return $next($model);
    }
}

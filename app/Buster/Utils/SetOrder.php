<?php

declare(strict_types=1);

namespace Railgun\Buster\Utils;

use Illuminate\Support\Collection;
use JsonException;
use stdClass;
use function array_reverse;
use function is_array;
use function is_string;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class SetOrder
{
    /** @var string[] The order keys. */
    private array $keys;
    /** @var string The internal state. */
    private string $state;
    /** @var int The order iterator. */
    private int $iterator;

    /**
     * @param string|string[]|null $keys The order keys.
     */
    public function __construct($keys = null)
    {
        if (is_string($keys)) {
            $this->keys = [$keys];
        } elseif ($keys === null) {
            $this->keys = [];
        } else {
            $this->keys = $keys;
        }

        $this->state    = '';
        $this->iterator = 0;
    }

    /**
     * @param Collection|stdClass[]|mixed[][] $models The source models
     *
     * @return Collection|stdClass[]|mixed[][] The ordered models.
     */
    public function __invoke(Collection $models): Collection
    {
        foreach (array_reverse($this->keys) as $key) {
            $models = $models->sortBy($key);
        }

        $this->iterator = 0;

        return $models->map(function ($model) {
            if ($this->state !== $this->getModelState($model)) {
                $this->iterator = 0;
                $this->state    = $this->getModelState($model);
            }

            if (is_array($model)) {
                $model['order'] = ++$this->iterator;
            } else {
                $model->order = ++$this->iterator;
            }

            return $model;
        });
    }

    /**
     * Get the state iterator using the model and the provided keys.
     *
     * @param stdClass|mixed[] $model The source model.
     *
     * @return string The state a a json string.
     */
    public function getModelState($model): string
    {
        $state = [];

        foreach ($this->keys as $key) {
            $state[$key] = is_array($model) ? $model[$key] : $model->$key;
        }

        try {
            return json_encode($state, JSON_THROW_ON_ERROR, 512);
        } catch (JsonException $exception) {
            return '';
        }
    }
}

<?php

namespace App\Services\Braintree\Models;

use App\Exceptions\Http404ModelNotFoundException;
use ArrayAccess;
use Braintree\Base;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Class BraintreeModel.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
abstract class BraintreeModel implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    /** @var Base $model */
    protected $model;
    /** @var array $attributes The custom model attributes */
    protected $attributes;

    public function __construct(Base $model)
    {
        $this->model = $model;

        // Cast common dates to Carbon
        if (isset($model->createdAt)) {
            $this->createdAt = new Carbon(
                $model->createdAt->format("Y-m-d H:i:s"),
                $model->createdAt->getTimezone()
                    ->getName()
            );
        }
        if (isset($model->updatedAt)) {
            $this->updatedAt = new Carbon(
                $model->updatedAt->format("Y-m-d H:i:s"),
                $model->updatedAt->getTimezone()
                    ->getName()
            );
        }
    }

    /**
     * Find a plan by its id or return null.
     *
     * @param string $id
     *
     * @return static
     */
    public static function find(string $id)
    {
        return static::findOr($id, function () {
        });
    }

    /**
     * The findOr function, must be reimplemented.
     *
     * @param string   $id
     * @param callable $callback
     *
     * @return mixed
     */
    abstract public static function findOr(string $id, callable $callback);

    /**
     * Find a plan by its id.
     *
     * @param string $id
     *
     * @return static
     */
    public static function findOrFail(string $id)
    {
        return static::findOr($id, function () {
            throw new Http404ModelNotFoundException();
        });
    }

    /**
     * Check if key exists in attributes, else check in the Braintree model.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->attributes[$key] ?? $this->model->{$key};
    }

    /**
     * Set the attribute.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Magic call.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->model, $name], $arguments);
    }

    /**
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset an offset to check for
     *
     * @return bool true on success or false on failure. The return value will be casted to boolean if non-boolean
     *              was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset the offset to retrieve
     *
     * @return mixed can return all value types
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset the offset to assign the value to
     * @param mixed $value  the value to set
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }

    /**
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset the offset to unset
     */
    public function offsetUnset($offset): void
    {
        if (isset($this[$offset])) {
            unset($this->attributes[$offset]);
        }
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the array representation of the Plan.
     *
     * @return array
     */
    public function toArray()
    {
        $rawModelAttributes = $this->model->jsonSerialize();

        foreach ($this->attributes as $key => $value) {
            if ($value instanceof Carbon) {
                $value = $value->__toString();
            }

            $rawModelAttributes[$key] = $value;
        }

        return $rawModelAttributes;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @see   http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by json_encode, which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}

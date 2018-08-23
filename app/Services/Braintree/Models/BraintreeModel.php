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
 * Class BraintreeModel
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree\Models
 * @since 4.1.0
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
                $model->createdAt->getTimezone()->getName()
            );
        }
        if (isset($model->updatedAt)) {
            $this->updatedAt = new Carbon(
                $model->updatedAt->format("Y-m-d H:i:s"),
                $model->updatedAt->getTimezone()->getName()
            );
        }
    }


    public function __get($key)
    {
        return $this->attributes[$key] ?? $this->model->{$key};
    }


    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this->model, $name)) {
            return call_user_func_array([$this->model, $name], $arguments);
        } else {
            return null;
        }
    }


    /**
     * The findOr function, must be reimplemented
     *
     * @param string $id
     * @param callable $callback
     *
     * @return mixed
     */
    abstract public static function findOr(string $id, callable $callback);


    /**
     * Find a plan by its id or return null
     *
     * @param string $id
     *
     * @return static
     */
    public static function find(string $id)
    {
        return static::findOr($id, function () {
            return null;
        });
    }


    /**
     * Find a plan by its id
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
     * Get the array representation of the Plan
     *
     * @return array
     */
    public function toArray()
    {
        $planData = $this->model->jsonSerialize();

        foreach ($this->attributes as $key => $value) {
            if ($value instanceof Carbon) {
                $value = $value->__toString();
            }

            $planData[$key] = $value;
        }

        return $planData;
    }


    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure. The return value will be casted to boolean if non-boolean
     * was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }


    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }


    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }


    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
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
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Data which can be serialized by json_encode, which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}

<?php

declare(strict_types=1);

namespace Mathrix\Silicate\Bricks;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator;
use function array_key_exists;
use function array_replace_recursive;
use function uuid_create;

/**
 * Base class for Silicate bricks.
 *
 * @property-read string $kind The brick kind.
 * @property-read string $uuid The brick id (UUIDv4).
 */
abstract class Brick implements ArrayAccess, Arrayable
{
    public const KIND = null;

    /** @var mixed[] $defaults The default brick attributes. */
    protected array $defaults = [];
    /** @var mixed[] $attributes The brick attributes. */
    protected array $attributes = [];
    /** @var mixed[] $rules The brick validation rules. */
    protected array $rules = [];

    /**
     * @param mixed[] $attributes The brick attributes.
     */
    public function __construct(array $attributes)
    {
        $this->fill($attributes);
    }

    /**
     * Set or merge the brick attributes.
     *
     * @param mixed[] $attributes The brick attributes.
     * @param bool    $merge      Merge with the existing attributes.
     */
    public function fill(array $attributes, bool $merge = true): void
    {
        if ($merge) {
            $theAttributes = array_replace_recursive($this->attributes, $attributes);
        } else {
            $theAttributes = $attributes;
        }

        if (!isset($theAttributes['id'])) {
            $theAttributes['id'] = uuid_create();
        }

        $theAttributes['kind'] = static::KIND;

        $this->attributes = array_replace_recursive($this->defaults, $theAttributes);
        $this->validate();
    }

    /**
     * Validate the brick attributes against its rules.
     */
    public function validate(): void
    {
        $defaultRules = [
            'kind' => 'required|string|in:' . $this->kind,
            'uuid' => 'required|string|uuid',
        ];

        $rules = array_replace_recursive($defaultRules, $this->rules);

        $validator = Validator::make($this->attributes, $rules);
        $validator->validate();
    }

    /**
     * @return mixed[] The Silicate Bricks.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param string|int $offset An offset to check for.
     *
     * @return bool true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param string|int $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    /**
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param string|int $offset The offset to assign the value to.
     * @param mixed      $value  The value to set.
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
        $this->validate();
    }

    /**
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param string|int $offset The offset to unset.
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @param string $name The property name.
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->offsetExists($name) ? $this->offsetGet($name) : null;
    }

    /**
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     */
    public function __set(string $name, $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param string $name The property name.
     *
     * @return bool If the property is set.
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}

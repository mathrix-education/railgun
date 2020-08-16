<?php

declare(strict_types=1);

namespace Railgun\Support;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonException;
use JsonSerializable;
use Throwable;
use Traversable;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function json_encode;
use function method_exists;
use const JSON_THROW_ON_ERROR;

/**
 * Base class for data classes.
 */
abstract class Container implements ArrayAccess, Arrayable, JsonSerializable, IteratorAggregate
{
    /** @var mixed[] $attributes The resource attributes. */
    protected array $attributes = [];
    /** @var string[] $appends The attributes to append when serializing and iterating. */
    protected array $appends = [];

    /**
     * @param mixed[] $attributes The initial attributes.
     */
    public function __construct(array $attributes = [])
    {
        $this->apply($attributes);
    }

    /**
     * @param mixed[] $attributes The attributes to apply.
     *
     * @return $this
     */
    public function apply(array $attributes): self
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this;
    }

    /**
     * @param string $attribute The attribute name.
     *
     * @return false|string
     */
    public function getter(string $attribute)
    {
        $getter = 'get' . Str::studly($attribute);

        return method_exists($this, $getter) ? $getter : false;
    }

    /**
     * @param string $attribute The attribute name.
     *
     * @return false|string
     */
    public function setter(string $attribute)
    {
        $getter = 'set' . Str::studly($attribute);

        return method_exists($this, $getter) ? $getter : false;
    }

    /**
     * @param string|int $attribute The attribute name.
     *
     * @return bool If the attribute exists.
     */
    public function issetAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes) || $this->getter($attribute);
    }

    /**
     * @param string $attribute The attribute name.
     *
     * @return mixed The attribute value.
     */
    public function getAttribute(string $attribute)
    {
        $getter = $this->getter($attribute);

        if ($getter) {
            return $this->{$getter}();
        }

        return $this->attributes[$attribute] ?? null;
    }

    /**
     * @param string $attribute The attribute name.
     * @param mixed  $value     The attribute new value.
     */
    public function setAttribute(string $attribute, $value): void
    {
        $setter = $this->setter($attribute);

        if ($setter) {
            $this->{$setter}($value);
        } else {
            $this->attributes[$attribute] = $value;
        }
    }

    /**
     * @param string $attribute The attribute name.
     */
    public function unsetAttribute(string $attribute): void
    {
        unset($this->attributes[$attribute]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_merge(array_keys($this->attributes), $this->appends));
    }

    /**
     * Get the array representation of the resource.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        try {
            $iterator = $this->getIterator();
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $iterator = [];
        }
        // @codeCoverageIgnoreEnd

        $export = [];

        foreach ($iterator as $attribute) {
            $value = $this->getAttribute($attribute);

            if ($value instanceof Arrayable) {
                $export[$attribute] = $value->toArray();
            } else {
                $export[$attribute] = $value;
            }
        }

        return $export;
    }

    /**
     * Get the JSON-able representation of the resource.
     *
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string|int $attribute The attribute name.
     *
     * @return bool If the attribute exists.
     */
    public function offsetExists($attribute): bool
    {
        return $this->issetAttribute($attribute);
    }

    /**
     * @param string|int $attribute The attribute name.
     *
     * @return mixed The attribute value.
     */
    public function offsetGet($attribute)
    {
        return $this->getAttribute($attribute);
    }

    /**
     * @param string|int $attribute The attribute name.
     * @param mixed      $value     The attribute new value.
     */
    public function offsetSet($attribute, $value): void
    {
        $this->setAttribute($attribute, $value);
    }

    /**
     * @param string|int $attribute The attribute name.
     */
    public function offsetUnset($attribute): void
    {
        $this->unsetAttribute($attribute);
    }

    /**
     * @param string $attribute The attribute name.
     *
     * @return bool If the attribute is set.
     */
    public function __isset(string $attribute): bool
    {
        return $this->issetAttribute($attribute);
    }

    /**
     * @param string $attribute The attribute name.
     *
     * @return mixed The attribute value.
     */
    public function __get(string $attribute)
    {
        return $this->getAttribute($attribute);
    }

    /**
     * @param string|int $attribute The attribute name.
     * @param mixed      $value     The attribute new value.
     */
    public function __set(string $attribute, $value): void
    {
        $this->setAttribute($attribute, $value);
    }

    /**
     * @param string|int $attribute The attribute name.
     */
    public function __unset($attribute): void
    {
        $this->unsetAttribute($attribute);
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return (string)json_encode($this, JSON_THROW_ON_ERROR, 512);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Support;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Iterator;
use stdClass;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_search;
use function count;

class LazyStdClass extends stdClass implements ArrayAccess, Arrayable, Iterator
{
    /** @var string|null The iterator key. */
    private ?string $key = null;
    /** @var Closure[] $resolvers */
    private array $resolvers;

    /**
     * @param Closure[] $resolvers The predefined resolvers.
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = array_map(static function ($val) {
            return $val instanceof Closure ? $val : static fn() => $val;
        }, $resolvers);
    }

    /**
     * @param string|int $offset The array offset.
     *
     * @return bool If the lazy class has a resolver for the given property name.
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->resolvers);
    }

    /**
     * @param string|int $offset The array offset.
     *
     * @return mixed The lazy-loaded value.
     */
    public function offsetGet($offset)
    {
        if (!$this->__isset($offset)) {
            return null;
        }

        return $this->resolvers[$offset]();
    }

    /**
     * @param string|int    $offset The array offset.
     * @param Closure|mixed $value  The value to lazy-load.
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Closure) {
            $value = static fn() => $value;
        }

        $this->resolvers[$offset] = $value;
    }

    /**
     * @param string|int $offset The array offset.
     */
    public function offsetUnset($offset): void
    {
        unset($this->resolvers[$offset]);
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->offsetGet($this->key);
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        $keys  = array_keys($this->resolvers);
        $index = array_search($this->key, $keys, true);

        if ($index >= count($keys) - 1) {
            // This is the last key
            $this->key = null;
        } else {
            $this->key = $keys[$index + 1];
        }
    }

    /**
     * Return the key of the current element
     *
     * @return string|null scalar on success, or null on failure.
     */
    public function key(): ?string
    {
        return $this->key;
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool if current position is valid.
     */
    public function valid(): bool
    {
        if ($this->key === null) {
            return false;
        }

        if (!$this->offsetExists($this->key)) {
            $this->key = null; // @codeCoverageIgnore
        }

        return true;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        $keys      = array_keys($this->resolvers);
        $this->key = $keys[0] ?? null;
    }

    /**
     * Get the instance as an array.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param string $name The property name.
     *
     * @return bool If the lazy class has a resolver for the given property name.
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * @param string $name The property name.
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name  The property name.
     * @param mixed  $value The associated resolver.
     */
    public function __set(string $name, $value): void
    {
        $this->offsetSet($name, $value);
    }
}

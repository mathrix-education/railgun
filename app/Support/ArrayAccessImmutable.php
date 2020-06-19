<?php

declare(strict_types=1);

namespace Railgun\Support;

use ArrayAccess;
use Railgun\Exceptions\ImmutableException;

/**
 * @mixin ArrayAccess
 */
trait ArrayAccessImmutable
{
    /**
     * @param string|int $offset
     *
     * @return bool If the offset exists.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    /**
     * @param string|int $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * @param string|int $offset
     * @param mixed      $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new ImmutableException(static::class . ' is immutable');
    }

    /**
     * @param string|int $offset
     */
    public function offsetUnset($offset): void
    {
        throw new ImmutableException(static::class . ' is immutable');
    }
}

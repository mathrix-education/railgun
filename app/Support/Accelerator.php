<?php

namespace Railgun\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @mixin Collection
 */
abstract class Accelerator
{
    protected Repository $cache;
    protected int        $cacheTtl = 3600;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    protected function getCacheKey(): string
    {
        return Str::slug(static::class);
    }

    /**
     * Fetch and populate the data.
     *
     * @return Collection
     */
    abstract protected function fetch(): Collection;

    protected function data()
    {
        return $this->cache->remember($this->getCacheKey(), $this->cacheTtl, fn() => $this->fetch());
    }

    public function flush(): void
    {
        $this->cache->forget($this->getCacheKey());
    }

    public function __call(string $name, array $args)
    {
        return $this->data()->{$name}(...$args);
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Allow models to have local in-memory cache.
 *
 * @mixin Model
 */
trait Cacheable
{
    /**
     * Cache data for the model.
     *
     * @param string  $key      The cache sub key name.
     * @param Closure $callback The cache callback.
     *
     * @return mixed The cached data.
     */
    public function cache(string $key, Closure $callback)
    {
        return Cache::store('request')->rememberForever($this->getCacheKey($key), $callback);
    }

    /**
     * Get the full qualified cache key.
     *
     * @param string $key The cache sun key.
     *
     * @return string The full qualified cache key.
     */
    protected function getCacheKey(string $key): string
    {
        return 'models.' . static::class . ".{$this->getKey()}.$key";
    }
}

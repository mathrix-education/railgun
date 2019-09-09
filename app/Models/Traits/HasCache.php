<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function class_basename;
use function is_callable;

/**
 * Allow to easily cache data for models.
 */
trait HasCache
{
    /**
     * Get the current model cache full key.
     *
     * @param string $key The data key.
     *
     * @return string The full cache key.
     */
    protected function getCacheKey(string $key) : string
    {
        $prefix = Str::slug(class_basename($this) . ' ' . $this->{$this->primaryKey}, '.');

        return "$prefix.$key";
    }

    /**
     * Cache a model data.
     *
     * @param string         $key        The cache key, will be prefixed by {model}.{primaryKey}
     * @param int            $ttlSeconds The cache TTL in seconds.
     * @param mixed|callable $value      The cache value.
     *
     * @return mixed The cached value.
     */
    public function cache(string $key, int $ttlSeconds, $value)
    {
        if (is_callable($value)) {
            return Cache::remember($this->getCacheKey($key), $ttlSeconds, $value);
        }

        Cache::add($this->getCacheKey($key), $ttlSeconds, $value);

        return $value;
    }

    /**
     * Invalidate the model cache.
     *
     * @param string|null $key The cache key to invalidate. By default, invalidate all the model cache.
     */
    public function invalidate(?string $key = '%') : void
    {
        DB::table('cache')
            ->where('key', 'like', $this->getCacheKey($key))
            ->delete();
    }
}

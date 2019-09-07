<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trait HasCache.
 * Allow to easily cache data for models.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
trait HasCache
{
    /**
     * Get the current model cache full key.
     *
     * @param string $key The data key.
     *
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = Str::slug(class_basename($this) . "." . $this->primaryKey);

        return "$prefix.$key";
    }


    /**
     * Cache a model data.
     *
     * @param string $key
     * @param int $ttlSeconds
     * @param mixed|callable $value
     *
     * @return mixed
     */
    public function cache(string $key, int $ttlSeconds, $value)
    {
        if (is_callable($value)) {
            return Cache::remember($this->getCacheKey($key), $ttlSeconds, $value);
        } else {
            Cache::add($this->getCacheKey($key), $ttlSeconds, $value);

            return $value;
        }
    }


    /**
     * Invalidate the model cache.
     *
     * @param string|null $key The cache key to invalidate. By default, invalidate all the model cache.
     */
    public function invalidate($key = "%"): void
    {
        DB::table("cache")
            ->where("key", "like", $this->getCacheKey($key))
            ->delete();
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Railgun\Models\Contracts\Cacheable as CacheableContract;
use function class_basename;

/**
 * @mixin CacheableContract
 */
trait Cacheable
{
    public function getCache(?string $store = null): TaggedCache
    {
        return Cache::store($store)->tags(['models', Str::lower(class_basename($this)), $this->getKey()]);
    }

    public function flushCache(?string $store = null): void
    {
        $this->getCache($store)->flush();
    }
}

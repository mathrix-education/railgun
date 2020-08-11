<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Allow models to have local in-memory cache.
 *
 * @mixin Model
 */
trait InteractsWithCache
{
    /**
     * Get the full qualified cache key.
     *
     * @param string $key The cache sun key.
     *
     * @return string The full qualified cache key.
     */
    protected function getCacheKey(string $key): string
    {
        return "models.{$this->getGlobalKey()}.$key";
    }
}

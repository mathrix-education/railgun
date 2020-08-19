<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

/**
 * @mixin \Railgun\Models\Contracts\Sellable
 */
trait Sellable
{
    public function getAvailableAttribute(): bool
    {
        return $this->sellable && ($this->attributes['available'] ?? $this->attributes['visible']);
    }

    public function getSellableAttribute(): bool
    {
        return $this->price !== null;
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Railgun\Models\BaseModel;

/**
 * @property      string $name      The product human-readable name.
 * @property      int    $price     The product price in cents.
 * @property      bool   $visible   If the product is visible.
 * @property      bool   $available If the product is visible.
 * ---
 * @property-read bool   $sellable  If the product is sellable.
 * @mixin BaseModel
 */
interface Sellable extends Ownable
{
    public function getAvailableAttribute(): bool;

    public function getSellableAttribute(): bool;
}

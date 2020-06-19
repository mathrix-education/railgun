<?php

declare(strict_types=1);

namespace Railgun\Billing\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string|int $id
 * @property      string     $name
 * @property      int        $price
 * @property      string     $stripe_product_id
 * @property      string     $stripe_price_id
 * @mixin Model
 */
interface Sellable
{
    public function getStripeProductIdAttribute(): string;
}

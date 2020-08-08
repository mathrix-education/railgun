<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;
use Railgun\Billing\Contracts\Product;

/**
 * @mixin Model
 * @mixin \Railgun\Billing\Stripe\Models\StripeProduct
 */
interface StripeProduct extends Product
{
    public function getStripeProductIdAttribute(): string;
}

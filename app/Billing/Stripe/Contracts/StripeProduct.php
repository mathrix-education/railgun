<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Contracts\Sellable;

/**
 * @mixin Model
 * @mixin \Railgun\Billing\Stripe\Models\StripeProduct
 */
interface StripeProduct extends Sellable
{
    public function getStripeProductIdAttribute(): string;
}

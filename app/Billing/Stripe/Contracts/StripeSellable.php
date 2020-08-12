<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Contracts\Sellable;

/**
 * @mixin Model
 * @mixin \Railgun\Billing\Stripe\Models\StripeSellable
 */
interface StripeSellable extends Sellable
{
    public function getStripeProductIdAttribute(): string;
}

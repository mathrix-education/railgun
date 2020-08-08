<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @mixin \Railgun\Billing\Stripe\Models\StripeProduct
 */
interface StripeProduct
{
    public function getStripeProductIdAttribute(): string;
}

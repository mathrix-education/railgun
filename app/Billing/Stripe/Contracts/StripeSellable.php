<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Contracts\Sellable;

/**
 * @property-read string $is_sellable
 * @property-read string $stripe_product_id
 * @property      string $stripe_price_id
 * @mixin Model
 */
interface StripeSellable extends Sellable
{
}

<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Contracts;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Contracts\Sellable;
use Stripe\Price;
use Stripe\Product;

/**
 * @property string       $stripe_product_id
 * @property string       $stripe_price_id
 * ---
 * @property-read Product $stripe_product
 * @property-read Price   $stripe_price
 * @mixin Model
 */
interface StripeSellable extends Sellable
{
    public function getStripeProductAttribute(): ?Product;

    public function getStripePriceAttribute(): ?Price;
}

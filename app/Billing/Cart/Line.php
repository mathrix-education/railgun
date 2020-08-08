<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Railgun\Billing\Stripe\Contracts\StripeProduct;
use Railgun\Models\Coupon;
use Railgun\Support\Resource\Resource;
use function class_basename;
use function sprintf;
use function strtolower;
use function trans_choice;
use function ucfirst;

/**
 * @property string $description
 * @property string $price
 */
class Line extends Resource
{
    public static function fromProduct(StripeProduct $model): Line
    {
        $line = new self();

        $line->description = sprintf(
            '%s : "%s"',
            ucfirst(trans_choice('models.' . strtolower(class_basename($model)), 1)),
            $model->name,
        );
        $line->price       = $model->price;

        return $line;
    }

    public static function fromCoupon(Coupon $coupon, int $amount): Line
    {
        $line = new self();

        $line->description = sprintf(
            '%s : "%s"',
            ucfirst(trans_choice('models.coupon', 1)),
            $coupon->id,
        );
        $line->price       = -$amount;

        return $line;
    }
}

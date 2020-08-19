<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Models\Coupon;
use Railgun\Support\Container;
use Stripe\Price;
use function class_basename;
use function sprintf;
use function strtolower;
use function trans_choice;
use function ucfirst;

/**
 * @property string $description
 * @property string $price
 */
class Line extends Container
{
    public static function fromProduct(StripeSellable $model): Line
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

    public static function fromPrice(Price $price): Line
    {
        $line = new self();

        $line->description = "{$price->nickname} : {$price->product->description}";
        $line->price       = $price->unit_amount;

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

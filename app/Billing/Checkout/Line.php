<?php

declare(strict_types=1);

namespace Railgun\Billing\Checkout;

use Railgun\Billing\Contracts\Sellable;
use Railgun\Billing\Products\Premium;
use Railgun\Models\Coupon;
use function class_basename;
use function sprintf;
use function strtolower;
use function trans_choice;
use function ucfirst;

class Line
{
    public string $description;
    public int    $price;

    public function __construct(string $name, int $price)
    {
        $this->description = $name;
        $this->price       = $price;
    }

    public static function fromPremium(): Line
    {
        return new self(
            Premium::PRODUCT['name'],
            Premium::PRICE_NO_SUB,
        );
    }

    public static function fromSellable(Sellable $model): Line
    {
        return new self(
            sprintf(
                '%s : "%s"',
                ucfirst(trans_choice('models.' . strtolower(class_basename($model)), 1)),
                $model->name,
            ),
            $model->price,
        );
    }

    public static function fromCoupon(Coupon $coupon, int $amount): Line
    {
        return new self(
            sprintf(
                '%s : "%s"',
                ucfirst(trans_choice('models.coupon', 1)),
                $coupon->id,
            ),
            -$amount,
        );
    }
}

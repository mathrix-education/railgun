<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Checkout;

use Illuminate\Support\Collection;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Stripe\Contracts\StripeProduct;
use Railgun\Models\Chapter;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Support\Resource\Resource;
use function is_string;

/**
 * @property      Collection|array<int, Chapter>               $chapters
 * @property      Collection|array<int, Pack>                  $packs
 * @property      Collection|array<int, Training>              $trainings
 * @property-read Collection|array<int, Chapter|Pack|Training> $products
 * @property-read Coupon|null                                  $coupon
 * @property-read Collection|array<int, Line>                  $lines
 * @property-read int                                          $subTotal
 * @property-read int                                          $couponAmount
 * @property-read int                                          $total
 */
class Cart extends Resource
{
    public const IDENTIFIER = 'cart_v11';

    private function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed[] $args The Cart GraphQL input.
     *
     * @return Cart The new Cart instance.
     */
    public static function build(array $args): Cart
    {
        $cart = new self();

        // Set the products
        $cart->chapters  = Chapter::query()->whereIn('id', $args['chapters'] ?? [0])->get();
        $cart->packs     = Pack::query()->whereIn('id', $args['packs'] ?? [0])->get();
        $cart->trainings = Training::query()->whereIn('id', $args['trainings'] ?? [0])->get();

        // Set the coupon code
        if (isset($args['coupon'])) {
            $cart->coupon = $args['coupon'];
        }

        return $cart;
    }

    public function validate(): self
    {
        return $this;
    }

    /**
     * @return Collection|StripeProduct[] The product collection.
     */
    protected function getProducts(): Collection
    {
        return (new Collection())
            ->merge($this->chapters)
            ->merge($this->packs)
            ->merge($this->trainings);
    }

    /**
     * @return Collection|array<int, Line>
     */
    protected function getLines(): Collection
    {
        $lines = $this->products->map(static function (StripeProduct $sellable) {
            return Line::fromProduct($sellable);
        });

        if ($this->couponAmount > 0) {
            $lines->push(Line::fromCoupon($this->coupon, $this->couponAmount));
        }

        return $lines;
    }

    /**
     * @return int The cart sub-total, which is the sum of the product prices without any applied discount.
     */
    protected function getSubTotal(): int
    {
        return $this->products->sum('price');
    }

    /**
     * @param Coupon|string $coupon The coupon.
     *
     * @return Cart
     */
    protected function setCoupon($coupon): self
    {
        if (is_string($coupon)) {
            /**
             * @var Coupon $coupon
             * @noinspection CallableParameterUseCaseInTypeContextInspection
             */
            $coupon = Coupon::query()->find($coupon);

            if ($coupon === null || !$coupon->valid) {
                throw new InvalidCouponCodeException($coupon->id);
            }
        }

        $this->attributes['coupon'] = $coupon;

        return $this;
    }

    /**
     * @return int Get the coupon amount as a positive integer.
     */
    protected function getCouponAmount(): int
    {
        if ($this->coupon === null) {
            return 0;
        }

        if ($this->coupon->type === Coupon::TYPE_ABSOLUTE) {
            return $this->coupon->value;
        }

        if ($this->coupon->type === Coupon::TYPE_PERCENT) {
            return $this->coupon->value;
        }

        return 0;
    }

    protected function getTotal(): int
    {
        return $this->subTotal - $this->couponAmount;
    }
}

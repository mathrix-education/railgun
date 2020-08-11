<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Support\Collection;
use Railgun\Billing\Contracts\Product;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Billing\Stripe\Contracts\StripeProduct;
use Railgun\Models\Chapter;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Railgun\Support\Resource\Resource;
use function array_unique;
use function is_string;

/**
 * @property      Collection|array<int, Chapter>  $chapters
 * @property      Collection|array<int, Pack>     $packs
 * @property      Collection|array<int, Training> $trainings
 * @property-read Collection|Product[]            $products
 * @property-read Coupon|null                     $coupon
 * @property-read Collection|array<int, Line>     $lines
 * @property-read int                             $subTotal
 * @property-read int                             $couponAmount
 * @property-read int                             $total
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

        // Set the products (use of `array_unique` solve duplicates)
        $cart->chapters  = Chapter::query()->whereIn('id', array_unique($args['chapters'] ?? [0]))->get();
        $cart->packs     = Pack::query()->whereIn('id', array_unique($args['packs'] ?? [0]))->get();
        $cart->trainings = Training::query()->whereIn('id', array_unique($args['trainings'] ?? [0]))->get();

        // Set the coupon code; coupon validation is handled here
        if (isset($args['coupon'])) {
            $cart->coupon = $args['coupon'];
        }

        return $cart;
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

    /**
     * @return int The final total of the cart.
     */
    protected function getTotal(): int
    {
        return $this->subTotal - $this->couponAmount;
    }

    /**
     * Validate the cart. A user may be provided to ensure that he does not buy a product he already owns.
     *
     * @param User|null $user The user to check.
     *
     * @return $this
     */
    public function validate(?User $user = null): self
    {
        // Check that all products are available
        $unavailable = $this->products->filter(static fn(Product $p) => !$p->available)->pluck('name');

        if ($unavailable->isNotEmpty()) {
            throw new UnavailableProducts($unavailable->toArray());
        }

        // Check that the user has not already bought one of the cart product
        $owned = $this->products->filter(static fn(Product $p) => $user->owns($p))->pluck('name');

        if ($owned->isNotEmpty()) {
            throw new UnavailableProducts($owned->toArray());
        }

        return $this;
    }
}

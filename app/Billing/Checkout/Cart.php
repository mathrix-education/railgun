<?php

declare(strict_types=1);

namespace Railgun\Billing\Checkout;

use Dotenv\Loader\Lines;
use ErrorException;
use Illuminate\Support\Collection;
use Railgun\Billing\Contracts\Sellable;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Products\Premium;
use Railgun\Models\Chapter;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use function array_key_exists;
use function get_class;

/**
 * @property-read Collection|Lines[] $lines
 * @property-read int                $subTotal
 * @property-read int                $total
 */
class Cart
{
    public const IDENTIFIER = 'cart_v7';

    /** @var Collection|Chapter[] $chapters */
    protected Collection $chapters;
    /** @var Collection|Pack[] $packs */
    protected Collection $packs;
    /** @var Collection|Training[] $trainings */
    protected Collection $trainings;
    /** @var Coupon|null $coupon The cart coupon. */
    protected ?Coupon    $coupon = null;
    /** @var bool $premium If the cart include the premium product. */
    protected bool       $premium;

    /**
     * @param mixed[] $args The Cart GraphQL input.
     */
    public function __construct(array $args)
    {
        // Set the products
        $this->chapters  = Chapter::query()->whereIn('id', $args['chapters'] ?? [0])->get();
        $this->packs     = Pack::query()->whereIn('id', $args['packs'] ?? [0])->get();
        $this->trainings = Training::query()->whereIn('id', $args['trainings'] ?? [0])->get();
        $this->premium   = $args['premium'] ?? false;

        // Set the coupon code
        if (array_key_exists('coupon', $args) && $args['coupon'] !== null) {
            $this->setCouponCode($args['coupon']);
        }
    }

    /**
     * @return Collection|Sellable[] The product collection.
     */
    public function getProducts(): Collection
    {
        return (new Collection())
            ->merge($this->chapters)
            ->merge($this->packs)
            ->merge($this->trainings);
    }

    /**
     * @return bool If the cart include the premium product.
     */
    public function withPremium(): bool
    {
        return $this->premium;
    }

    /**
     * @return int The cart sub-total, which is the sum of the product prices without any applied discount.
     */
    public function getSubTotal(): int
    {
        $sum = $this->getProducts()->sum(static function (Sellable $product) {
            return $product->price;
        });

        if ($this->withPremium()) {
            $sum += Premium::PRICE_NO_SUB;
        }

        return $sum;
    }

    /**
     * Get the cart coupon, if defined.
     *
     * @return Coupon|null The associated coupon.
     */
    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    /**
     * @param string $couponCode The coupon code.
     *
     * @return Cart
     */
    public function setCouponCode(string $couponCode): self
    {
        /** @var Coupon $coupon */
        $coupon = Coupon::query()->find($couponCode);

        if ($coupon === null || !$coupon->valid) {
            throw new InvalidCouponCodeException($couponCode);
        }

        $this->coupon = $coupon;

        return $this;
    }

    /**
     * @return int Get the coupon amount as a positive integer.
     */
    public function getCouponAmount(): int
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

    public function getTotal(): int
    {
        return $this->getSubTotal() - $this->getCouponAmount();
    }

    /**
     * @param string $name The property name
     *
     * @return mixed The resolved property value.
     *
     * @throws ErrorException
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'lines':
                $lines = $this->getProducts()->map(static function (Sellable $sellable) {
                    return Line::fromSellable($sellable);
                });

                if ($this->withPremium()) {
                    $lines->push(Line::fromPremium());
                }

                if ($this->getCouponAmount() > 0) {
                    $lines->push(Line::fromCoupon($this->getCoupon(), $this->getCouponAmount()));
                }

                return $lines;
            case 'subTotal':
                return $this->getSubTotal();
            case 'total':
                return $this->getTotal();
            default:
                throw new ErrorException('Undefined property ' . get_class($name) . '::$' . $name);
        }
    }

    /**
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     */
    public function __set(string $name, $value): void
    {
        // do nothing as cart is immutable
    }

    /**
     * @param string $name The property name.
     *
     * @return bool If the property is sett.
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function __isset(string $name): bool
    {
        try {
            return $this->{$name} !== null;
        } catch (ErrorException $exception) {
            return false;
        }
    }
}

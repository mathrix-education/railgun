<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Chapter;
use Railgun\Models\Contracts\Ownable;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Railgun\Support\Resource\Resource;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use function app;
use function array_unique;
use function config;
use function is_string;
use function round;

/**
 * @property      Collection|array<int, Chapter>  $chapters
 * @property      Collection|array<int, Pack>     $packs
 * @property      Collection|array<int, Training> $trainings
 * @property-read Collection|Sellable[]           $products
 * @property-read Price|null                      $plan
 * @property-read Coupon|null                     $coupon
 * @property-read Collection|array<int, Line>     $lines
 * @property-read int                             $subTotal
 * @property-read int                             $couponAmount
 * @property-read int                             $total
 */
class Cart extends Resource
{
    use SerializesModels;

    public const IDENTIFIER = 'cart_laser';

    protected ModeMachine $modeMachine;

    protected function __construct()
    {
        parent::__construct();

        $this->modeMachine = app()->make(ModeMachine::class);
    }

    /**
     * @param mixed[] $args The Cart GraphQL input.
     *
     * @return Cart The new Cart instance.
     *
     * @throws ApiErrorException
     */
    public static function build(array $args): Cart
    {
        $cart = new self();

        // Set the products (use of `array_unique` solve duplicates)
        $cart->chapters  = Chapter::query()->whereIn('id', array_unique($args['chapters'] ?? [0]))->get();
        $cart->packs     = Pack::query()->whereIn('id', array_unique($args['packs'] ?? [0]))->get();
        $cart->trainings = Training::query()->whereIn('id', array_unique($args['trainings'] ?? [0]))->get();

        if (isset($args['plan'])) {
            $cart->plan = $cart->modeMachine->get($args['plan']);
        }

        // Set the coupon code; coupon validation is handled here
        if (isset($args['coupon'])) {
            $cart->coupon = $args['coupon'];
        }

        return $cart;
    }

    /**
     * @return Collection|StripeSellable[] The product collection.
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
        $lines = $this->products->map(static function (StripeSellable $sellable) {
            return Line::fromProduct($sellable);
        });

        if ($this->plan) {
            $lines->push(Line::fromPrice($this->plan));
        }

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
        $sum = $this->products->sum('price');

        if ($this->plan) {
            $sum += $this->plan->unit_amount;
        }

        return $sum;
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
            return (int)round($this->coupon->value / 100 * $this->subTotal);
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
        $unavailable = $this->products->filter(static fn(Sellable $p) => !$p->available)->pluck('name');

        if ($unavailable->isNotEmpty()) {
            throw new UnavailableProducts($unavailable->toArray());
        }

        // Check that the user has not already bought one of the cart product
        $owned = $this->products->filter(static fn(Ownable $p) => $user->owns($p))->pluck('name');

        if ($owned->isNotEmpty()) {
            throw new UnavailableProducts($owned->toArray());
        }

        return $this;
    }

    /**
     * @return string[] The Stripe metadata cart representation.
     */
    public function toMetadata(): array
    {
        return [
            'identifier' => self::IDENTIFIER,
            'release'    => config('app.release'),
            'chapters'   => $this->chapters->pluck('id')->implode(','),
            'packs'      => $this->packs->pluck('id')->implode(','),
            'trainings'  => $this->trainings->pluck('id')->implode(','),
            'plan'       => $this->plan,
        ];
    }
}

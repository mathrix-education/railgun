<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Railgun\Billing\Exceptions\AlreadyBoughtException;
use Railgun\Billing\Exceptions\DuplicateProductsException;
use Railgun\Billing\Exceptions\EmptyCartException;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProductsException;
use Railgun\Billing\Stripe\Contracts\StripeSellable;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Chapter;
use Railgun\Models\Contracts\Ownable;
use Railgun\Models\Contracts\Sellable;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Railgun\Support\Container;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use function app;
use function array_unique;
use function config;
use function explode;
use function in_array;
use function is_string;
use function round;

/**
 * @property      Collection|Chapter[]  $chapters
 * @property      Collection|Pack[]     $packs
 * @property      Collection|Training[] $trainings
 * @property-read Collection|Sellable[] $products
 * @property-read Price|null            $plan
 * @property-read Coupon|null           $coupon
 * @property-read Collection|Line[]     $lines
 * @property-read int                   $subTotal
 * @property-read int                   $couponAmount
 * @property-read int                   $total
 */
class Cart extends Container
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
     * @param mixed[] $cartInput The CartInput GraphQL input.
     *
     * @return Cart The new Cart instance.
     *
     * @throws ApiErrorException
     */
    public static function build(array $cartInput): Cart
    {
        $cart = new self();

        // Set the products (use of `array_unique` solve duplicates)
        $cart->chapters  = Chapter::query()->whereIn('id', array_unique($cartInput['chapters'] ?? [0]))->get();
        $cart->packs     = Pack::query()->whereIn('id', array_unique($cartInput['packs'] ?? [0]))->get();
        $cart->trainings = Training::query()->whereIn('id', array_unique($cartInput['trainings'] ?? [0]))->get();

        if (isset($cartInput['plan'])) {
            $cart->plan = $cart->modeMachine->get($cartInput['plan']);
        }

        // Set the coupon code; coupon validation is handled here
        if (isset($cartInput['coupon'])) {
            $cart->coupon = $cartInput['coupon'];
        }

        return $cart;
    }

    /**
     * @see Cart::toMetadata
     *
     * @param mixed[] $metadata The cart metadata.
     *
     * @return Cart The new Cart instance.
     *
     * @throws ApiErrorException
     */
    public static function fromMetadata(array $metadata): Cart
    {
        $args = [];

        if (isset($metadata['chapters']) && $metadata['chapters'] !== '') {
            $args['chapters'] = explode(',', $metadata['chapters'] ?? '');
        }

        if (isset($metadata['packs']) && $metadata['packs'] !== '') {
            $args['packs'] = explode(',', $metadata['packs'] ?? '');
        }

        if (isset($metadata['trainings']) && $metadata['trainings'] !== '') {
            $args['trainings'] = explode(',', $metadata['trainings'] ?? '');
        }

        if (isset($metadata['plan'])) {
            $args['plan'] = $metadata['plan'];
        }

        return static::build($args);
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
     * @return Collection|Line[]
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
            $couponId = $coupon;

            /**
             * @var Coupon $coupon
             * @noinspection CallableParameterUseCaseInTypeContextInspection
             */
            $coupon = Coupon::query()->find($coupon);

            if ($coupon === null || !$coupon->valid) {
                throw new InvalidCouponCodeException($couponId);
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

        if ($this->coupon->type === Coupon::TYPE_PERCENT) {
            return (int)round($this->coupon->value / 100 * $this->subTotal);
        }

        return $this->coupon->value;
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
        // Check that the cart is not empty
        if ($this->plan === null && $this->products->isEmpty()) {
            throw new EmptyCartException();
        }

        // Check that there are no collisions between packs and trainings
        $trainingPackIds = $this->packs
            ->map(static fn(Pack $pack) => $pack->trainings->pluck('id'))
            ->flatten()
            ->toArray();
        $duplicates      = $this->trainings
            ->filter(static fn(Training $training) => in_array($training->id, $trainingPackIds, true));

        if ($duplicates->isNotEmpty()) {
            throw new DuplicateProductsException($duplicates->pluck('name')->toArray());
        }

        // Check that all products are available
        $unavailable = $this->products->reject(static fn(Sellable $p) => $p->available)->pluck('name');

        if ($unavailable->isNotEmpty()) {
            throw new UnavailableProductsException($unavailable->toArray());
        }

        // Check that the user has not already bought one of the cart product
        if ($user) {
            $owned = $this->products->filter(static fn(Ownable $p) => $user->owns($p))->pluck('name');

            if ($owned->isNotEmpty()) {
                throw new AlreadyBoughtException($owned->toArray(), $user->id);
            }
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
            'plan'       => $this->plan ? $this->plan->lookup_key : null,
        ];
    }
}

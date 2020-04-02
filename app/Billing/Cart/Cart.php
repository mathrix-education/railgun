<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Railgun\Billing\Models\Premium;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use function round;

/**
 * Represents the user cart (products + discounts).
 */
class Cart
{
    public const IDENTIFIER = 'cart_v7';

    /** @var User|null The user associated with the cart. */
    public ?User $user;
    /** @var mixed[] $input The input from the GraphQL query. */
    public array $input;
    /** @var Collection|Pack[] $packs The packs in the cart. */
    public Collection $packs;
    /** @var Collection|Training[] $trainings The trainings in the cart. */
    public Collection $trainings;
    /** @var Premium|null If the cart contains a premium product, it will be not null. */
    public ?Premium $premium;
    /** @var Coupon|null If the cart contains a valid coupon code, it will be not null. */
    public ?Coupon $coupon;

    /**
     * @param mixed[]   $input The input from the GraphQL query.
     * @param User|null $user  The user associated with the cart. If it is not null, additional checks such as product
     *                         eligibility will be performed.
     */
    public function __construct(array $input, ?User $user = null)
    {
        $this->setInput($input);
        $this->user = $user;
        $this->fetch();
    }

    /**
     * Validate the cart using the {@see CartValidator}.
     *
     * @return $this
     */
    public function validate(): self
    {
        (new CartValidator($this))->validate();

        return $this;
    }

    /**
     * Set a complete the input from the GraphQL query.
     *
     * @param mixed[] $input The input from the GraphQL query.
     */
    private function setInput(array $input): void
    {
        $input['packIds']     ??= [];
        $input['trainingIds'] ??= [];
        $input['premium']     ??= false;
        $input['couponCode']  ??= null;

        $this->input = $input;
    }

    /**
     * Fetch the cart models from the database, based on the passed $args.
     */
    private function fetch(): void
    {
        $this->packs     = Pack::query()
            ->with(['trainings:trainings.id'])
            ->whereIn('packs.id', $this->input['packIds'])
            ->get();
        $this->trainings = Training::query()->whereIn('id', $this->input['trainingIds'])->get();
        $this->premium   = $this->input['premium'] ? new Premium() : null;
        $this->coupon    = Coupon::query()->find($this->input['couponCode']);
    }

    /**
     * Get the cart products as a single {@see BaseCollection}.
     *
     * @return BaseCollection|Pack[]|Training[]
     */
    public function getProducts(): BaseCollection
    {
        return $this->packs->toBase()
            ->merge($this->trainings)
            ->push($this->premium)
            ->filter(static fn($p) => $p !== null);
    }

    /**
     * Get the total price in cents of the cart, without the coupon.
     *
     * @return int The subtotal in cents.
     */
    public function getSubTotal(): int
    {
        return $this->packs->sum('price')
            + $this->trainings->sum('price')
            + ($this->premium ? $this->premium->price : 0);
    }

    /**
     * Get the total price in cents of the cart, with the coupon.
     *
     * @return int The total price in cents.
     */
    public function getTotal(): int
    {
        if ($this->coupon === null) {
            return $this->getSubTotal();
        }

        if ($this->coupon->type === 'percentage') {
            return (int)round((100 - $this->coupon->value) / 100 * $this->getSubTotal());
        }

        return $this->getSubTotal() - $this->coupon->value;
    }

    /**
     * @return mixed[] Get the GraphQL type Cart representation.
     */
    public function toGraphQL(): array
    {
        return [
            'packs'     => $this->packs,
            'trainings' => $this->trainings,
            'coupon'    => $this->coupon,
            'subTotal'  => $this->getSubTotal(),
            'total'     => $this->getTotal(),
        ];
    }
}

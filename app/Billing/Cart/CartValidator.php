<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Railgun\Billing\Exceptions\AlreadyBoughtException;
use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Exceptions\DuplicateProductsException;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use function collect;

/**
 * Validate a {@see Cart}.
 */
class CartValidator
{
    private Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Run the full validation pipeline.
     *
     * @return $this
     */
    public function validate(): self
    {
        $this->checkDuplicates();
        $this->checkAvailability();
        $this->checkCouponCode();
        $this->checkOwnership();
        $this->checkPremium();

        return $this;
    }

    /**
     * Check if there is any product duplicate (for example, if there is a pack which contains a product which is
     * also in the cart).
     */
    private function checkDuplicates(): void
    {
        $trainingsIds     = $this->cart->trainings->pluck('id')->toArray();
        $packTrainingsIds = $this->cart->packs
            ->map(static fn(Pack $pack) => $pack->trainings)
            ->flatten(1)
            ->pluck('id')
            ->toArray();

        $ids = collect([...$trainingsIds, ...$packTrainingsIds]);

        $duplicateIds = $ids->duplicates();

        if ($duplicateIds->isNotEmpty()) {
            $duplicateNames = $this->cart->trainings->whereIn('id', $duplicateIds)->pluck('name');
            throw new DuplicateProductsException($duplicateNames->toArray());
        }
    }

    /**
     * Check that already products in the cart are available.
     */
    private function checkAvailability(): void
    {
        $unavailableNames = $this->cart->getProducts()
            ->reject(static fn($p) => $p->available)
            ->pluck('names');

        if ($unavailableNames->isNotEmpty()) {
            throw new UnavailableProducts($unavailableNames->toArray());
        }
    }

    /**
     * Check that if there is a coupon code, the associated {@see Coupon} is valid.
     */
    private function checkCouponCode(): void
    {
        if ($this->cart->input['coupon'] === null) {
            // No coupon code was provided, skip
            return;
        }

        if (
            ($this->cart->input['coupon'] !== null && $this->cart->coupon === null) // The coupon could not be found
            || $this->cart->coupon->expired // The coupon has expired
            || $this->cart->coupon->exhausted // The coupon is exhausted
        ) {
            throw new InvalidCouponCodeException($this->cart->input['coupon']);
        }
    }

    /**
     * If there is a user associated with cart, check that he does not own any of the products.
     */
    public function checkOwnership(): void
    {
        if ($this->cart->user === null) {
            // Skip this check if there is no user set.
            return;
        }

        $invalid = collect([
            ...$this->cart->trainings->filter(function (Training $training) {
                return $this->cart->user->owns($training);
            }),
            ...$this->cart->packs->filter(function (Pack $pack) {
                return $this->cart->user->owns($pack);
            }),
        ]);

        if ($invalid->isNotEmpty()) {
            throw new AlreadyBoughtException(
                $invalid->pluck('name')->toArray(),
                $this->cart->user->id,
            );
        }
    }

    /**
     * If there is a user associated with the cart and premium is selected, check that the user is not already premium.
     */
    public function checkPremium(): void
    {
        if ($this->cart->user === null) {
            // Skip this check if there is no user set.
            return;
        }

        if ($this->cart->premium !== null && $this->cart->user->premium) {
            throw new AlreadyPremiumException($this->cart->user->id);
        }
    }
}

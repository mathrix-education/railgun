<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Railgun\Billing\Exceptions\DuplicateProducts;
use Railgun\Billing\Exceptions\InvalidCouponCode;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Models\Pack;

class CartValidator
{
    private Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function validate(): self
    {
        $this->checkDuplicates();
        $this->checkAvailability();
        $this->checkCouponCode();
        $this->checkOwnership();
        $this->checkPremium();

        return $this;
    }

    private function checkDuplicates(): void
    {
        // Check that there is no pack <=> training collision
        $seenIds = $this->cart->packs
            ->map(fn(Pack $pack) => $pack->trainings->pluck('id'))
            ->flatten();
        $seenIds->merge($this->cart->trainings->pluck('id'));

        $duplicates = $seenIds->duplicates();

        if ($duplicates->isNotEmpty()) {
            throw new DuplicateProducts($duplicates->toArray());
        }
    }

    private function checkAvailability(): void
    {
        $unavailable = $this->cart->getProducts()
            ->reject(fn($p) => $p->available)
            ->pluck('id');

        if ($unavailable->isNotEmpty()) {
            throw new UnavailableProducts($unavailable->toArray());
        }
    }

    private function checkCouponCode(): void
    {
        if ($this->cart->input['couponCode'] === null) {
            // No coupon code was provided
            return;
        }

        if ($this->cart->input['couponCode'] !== null && $this->cart->coupon === null) {
            // The coupon could not be found
            throw new InvalidCouponCode($this->cart->input['couponCode']);
        }

        if (!$this->cart->coupon->expired) {
            // The coupon has expired
            throw new InvalidCouponCode($this->cart->input['couponCode']);
        }

        if (!$this->cart->coupon->exhausted) {
            // The coupon is exhausted
            throw new InvalidCouponCode($this->cart->input['couponCode']);
        }
    }

    public function checkOwnership(): void
    {
        if ($this->cart->user === null) {
            // Skip this check if there is no user set.
            return;
        }
    }

    public function checkPremium(): void
    {
        if ($this->cart->user === null) {
            // Skip this check if there is no user set.
            return;
        }
    }
}

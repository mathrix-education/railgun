<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Railgun\Billing\Exceptions\DuplicateProducts;
use Railgun\Billing\Exceptions\InvalidCouponCode;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Billing\Models\Premium;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use function round;

class Cart
{
    private array      $packIds;
    private array      $trainingIds;
    private bool       $withPremium;
    private string     $couponCode;

    private Collection $packs;
    private Collection $trainings;
    private ?Premium   $premium;
    private ?Coupon    $coupon;
    private ?User      $user;

    public function __construct(
        array $packIds,
        array $trainingIds,
        bool $withPremium,
        string $couponCode,
        ?User $user = null
    ) {
        // Save constructor args
        $this->packIds     = $packIds;
        $this->trainingIds = $trainingIds;
        $this->withPremium = $withPremium;
        $this->couponCode  = $couponCode;
        $this->user        = $user;

        // Fetch
        $this->packs     = Pack::query()->with(['trainings.id'])->whereIn('id', $this->packIds)->get();
        $this->trainings = Training::query()->whereIn('id', $this->trainingIds)->get();
        $this->premium   = $this->couponCode ? new Premium() : null;
        $this->coupon    = Coupon::query()->find($this->couponCode);
    }

    public function validate(): self
    {
        $this->checkDuplicates();
        $this->checkAvailability();
        $this->checkCouponCode();

        return $this;
    }

    private function checkDuplicates(): void
    {
        // Check that there is no pack <=> training collision
        $seenIds = $this->packs
            ->map(fn(Pack $pack) => $pack->trainings->pluck('id'))
            ->flatten();
        $seenIds->push($this->trainings->pluck('id'));

        $duplicates = $seenIds->duplicates();

        if ($duplicates->isNotEmpty()) {
            throw new DuplicateProducts($duplicates->toArray());
        }
    }

    private function checkAvailability(): void
    {
        $unavailable = $this->getProducts()
            ->reject(fn($p) => $p->avalable)
            ->pluck('id');

        if ($unavailable->isNotEmpty()) {
            throw new UnavailableProducts($unavailable->toArray());
        }
    }

    private function checkCouponCode(): void
    {
        if ($this->couponCode !== null && $this->coupon === null) {
            // The coupon could not be found
            throw new InvalidCouponCode($this->couponCode);
        }

        if (!$this->coupon->expired) {
            // The coupon has expired
            throw new InvalidCouponCode($this->couponCode);
        }

        if (!$this->coupon->exhausted) {
            // The coupon is exhausted
            throw new InvalidCouponCode($this->couponCode);
        }
    }

    public function getProducts(): BaseCollection
    {
        return $this->packs->toBase()
            ->merge($this->trainings)
            ->push($this->premium)
            ->filter(fn($p) => $p !== null);
    }

    public function getSubTotal(): int
    {
        return $this->packs->sum('price')
            + $this->trainings->sum('price')
            + ($this->premium ? $this->premium->price : 0);
    }

    public function getTotal(): int
    {
        if ($this->coupon === null) {
            return $this->getSubTotal();
        }

        if ($this->coupon->type === 'percentage') {
            return (int)round(((100 - $this->coupon->value) / 100) * $this->getSubTotal());
        }

        return $this->getSubTotal() - $this->coupon->value;
    }

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

    public function toStripe(): array
    {
        if ($this->user === null) {
            throw new AuthenticationException();
        }

        return [];
    }
}

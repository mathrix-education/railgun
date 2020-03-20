<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Railgun\Billing\Exceptions\DuplicateItems;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use function round;

class Cart
{
    private Collection $packs;
    private Collection $trainings;
    private ?Premium   $premium;
    private ?Coupon    $coupon;
    private ?User      $user;

    public function __construct(
        array $packIds,
        array $trainingIds,
        bool $premium,
        string $coupon,
        ?User $user = null
    ) {
        // Fetch
        $this->packs     = Pack::query()->with(['trainings.id'])->whereIn('id', $packIds)->get();
        $this->trainings = Training::query()->whereIn('id', $trainingIds)->get();
        $this->premium   = $premium ? new Premium() : null;
        $this->coupon    = Coupon::query()->find($coupon);
        $this->user      = $user;

        $this->validate();
    }

    private function validate(): void
    {
        $this->checkDuplicates();
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
            throw new DuplicateItems($duplicates->toArray());
        }

        unset($duplicates);
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

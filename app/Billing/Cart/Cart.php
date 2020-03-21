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

class Cart
{
    public ?User      $user;
    public array      $input;
    public Collection $packs;
    public Collection $trainings;
    public ?Premium   $premium;
    public ?Coupon    $coupon;

    public function __construct(array $input, ?User $user = null)
    {
        $this->setInput($input);
        $this->user = $user;
        $this->fetch();
    }

    public function validate(): self
    {
        (new CartValidator($this))->validate();

        return $this;
    }

    private function setInput(array $input): void
    {
        $input['packIds']     ??= [];
        $input['trainingIds'] ??= [];
        $input['premium']     ??= false;
        $input['couponCode']  ??= null;

        $this->input = $input;
    }

    private function fetch(): void
    {
        $this->packs     = Pack::query()->with(['trainings:id'])->whereIn('id', $this->input['packIds'])->get();
        $this->trainings = Training::query()->whereIn('id', $this->input['trainingIds'])->get();
        $this->premium   = $this->input['premium'] ? new Premium() : null;
        $this->coupon    = Coupon::query()->find($this->input['couponCode']);
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
}

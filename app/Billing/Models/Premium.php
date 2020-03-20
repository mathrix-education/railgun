<?php

declare(strict_types=1);

namespace Railgun\Billing\Cart;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Railgun\Billing\Models\Plan;
use function method_exists;
use function trans;

/**
 * @property-read string $name
 * @property-read string $description
 * @property-read int    $price
 */
class Premium implements Arrayable
{
    public function getName(): string
    {
        return trans('cart.premium.name');
    }

    public function getDescription(): string
    {
        return trans('cart.premium.description', [
            'end' => $this->getEndDate()->format('d/m/Y'),
        ]);
    }

    public function getEndDate(): Carbon
    {
        return Plan::getProrationAnchor(Plan::ANNUALLY_PLAN);
    }

    public function getPrice(): int
    {
        return 20 * 100; // 20 euros flat
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
        ];
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        $method = 'get' . Str::studly($name);

        if (method_exists($this, $method)) {
            return $method();
        }

        return null;
    }
}

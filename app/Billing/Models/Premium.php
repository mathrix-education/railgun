<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use function method_exists;
use function trans;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read bool   $available
 * @property-read int    $price
 * @property-read Carbon $end_date
 */
class Premium implements Arrayable
{
    public function getId(): string
    {
        return 'premium-' . $this->getEndDate()->year;
    }

    public function getName(): string
    {
        return trans('cart.premium.name', [
            'end' => $this->getEndDate()->format('d/m/Y'),
        ]);
    }

    public function getAvailable(): bool
    {
        return true;
    }

    public function getPrice(): int
    {
        return 20 * 100; // 20 euros flat
    }

    public function getEndDate(): Carbon
    {
        return Plan::getProrationAnchor(Plan::ANNUALLY_PLAN);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'available'   => $this->available,
            'price'       => $this->price,
            'end_data'    => $this->end_date,
        ];
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        $method = 'get' . Str::studly($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
}

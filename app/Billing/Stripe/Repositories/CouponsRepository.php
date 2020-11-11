<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Repositories;

class CouponsRepository
{
    public const COMBO_MONTH = 'combo_month';
    public const DATA        = [
        self::COMBO_MONTH => [
            'id'         => self::COMBO_MONTH,
            'duration'   => 'forever',
            'amount_off' => 1790,
            'currency'   => 'eur',
            'name'       => 'Promotion Mode Machine & Mathrix Academy',
            'applies_to' => ['products' => [ProductsRepository::MODE_MACHINE, ProductsRepository::ACADEMY]],
        ],
    ];
}

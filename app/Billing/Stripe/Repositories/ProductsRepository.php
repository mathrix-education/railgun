<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Repositories;

class ProductsRepository
{
    public const  MODE_MACHINE = 'mode_machine';
    public const  ACADEMY      = 'academy';
    public const  DATA         = [
        self::MODE_MACHINE => [
            'id'          => self::MODE_MACHINE,
            'name'        => 'Mode Machine',
            'active'      => true,
            'description' => 'Mode Machine',
        ],
        self::ACADEMY      => [
            'id'          => self::ACADEMY,
            'name'        => 'Mathrix Academy',
            'active'      => true,
            'description' => 'Mathrix Academy',
        ],
    ];
}

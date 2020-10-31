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
            'description' => 'Accès au contenu premium de Mathrix.fr en illimité.',
        ],
        self::ACADEMY      => [
            'id'          => self::ACADEMY,
            'name'        => 'Academy',
            'active'      => true,
            'description' => 'Accès au contenu des lives.',
        ],
    ];
}

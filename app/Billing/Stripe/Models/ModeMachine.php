<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

/**
 * Stripe definitions for the "Mode Machine" premium plan.
 */
class ModeMachine
{
    public const PRODUCT = [
        'id'          => 'mode_machine',
        'name'        => 'Mode Machine',
        'active'      => true,
        'description' => 'Accès au contenu premium de Mathrix.fr en illimité.',
    ];
    public const PRICES  = [
        'v11_month'    => [
            'currency'    => 'eur',
            'lookup_key'  => 'v11_month',
            'nickname'    => 'Plan mensuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 1790,
            // GraphQL
            'period' => 'month',
            'months_per_period' => 1,
        ],
        'v11_quarter'  => [
            'currency'    => 'eur',
            'lookup_key'  => 'v11_quarter',
            'nickname'    => 'Plan trimestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 3,
            ],
            'unit_amount' => 5370,
            // GraphQL
            'period' => 'quarter',
            'months_per_period' => 3,
        ],
        'v11_semester' => [
            'currency'    => 'eur',
            'lookup_key'  => 'v11_semester',
            'nickname'    => 'Plan semestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 6,
            ],
            'unit_amount' => 10740,
            // GraphQL
            'period' => 'semester',
            'months_per_period' => 6,
        ],
        'v11_year'     => [
            'currency'    => 'eur',
            'lookup_key'  => 'v11_year',
            'nickname'    => 'Plan annuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 15480,
            // GraphQL
            'period' => 'year',
            'months_per_period' => 6,
        ],
    ];
}

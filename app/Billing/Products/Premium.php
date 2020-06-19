<?php

declare(strict_types=1);

namespace Railgun\Billing\Products;

use Carbon\Carbon;

class Premium
{
    public const PRODUCT      = [
        'id'          => 'mode_machine',
        'name'        => 'Mode Machine',
        'active'      => true,
        'description' => 'Accès au contenu premium de Mathrix.fr en illimité.',
    ];
    public const PRICES       = [
        'v7_month'    => [
            'currency'    => 'eur',
            'lookup_key'  => 'v7_month',
            'nickname'    => 'Plan mensuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 1790,
        ],
        'v7_quarter'  => [
            'currency'    => 'eur',
            'lookup_key'  => 'v7_quarter',
            'nickname'    => 'Plan trimestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 3,
            ],
            'unit_amount' => 5370,
        ],
        'v7_semester' => [
            'currency'    => 'eur',
            'lookup_key'  => 'v7_semester',
            'nickname'    => 'Plan semestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 6,
            ],
            'unit_amount' => 10740,
        ],
        'v7_year'     => [
            'currency'    => 'eur',
            'lookup_key'  => 'v7_year',
            'nickname'    => 'Plan annuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 15480,
        ],
    ];
    public const PRICE_NO_SUB = 1000;
    public const ANCHOR       = '31/07';

    /**
     * @see Premium::ANCHOR
     *
     * @return Carbon The next subscription anchor.
     */
    public static function getAnchor(): Carbon
    {
        $anchor = Carbon::createFromFormat('d/m', self::ANCHOR)->setTime(0, 0, 0);

        if ($anchor->isPast()) {
            $anchor = $anchor->addYear();
        }

        return $anchor;
    }
}

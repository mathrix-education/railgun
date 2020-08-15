<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Stripe\Price;
use Stripe\StripeClient;
use function app;
use function array_keys;
use function collect;

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
            'unit_amount' => 2700,
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
            'unit_amount' => 6700,
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
            'unit_amount' => 9700,
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
            'unit_amount' => 16700,
        ],
    ];

    /**
     * @return Collection|Price[] The "Mode Machine" prices.
     */
    public function all(): Collection
    {
        return Cache::remember('stripe:plans', 3600, static function () {
            return collect(
                app()
                    ->make(StripeClient::class)
                    ->prices
                    ->all([
                        'lookup_keys' => array_keys(self::PRICES),
                        'expand'      => ['product'],
                    ])
                    ->data,
            );
        });
    }

    /**
     * @param string $idOrLookupKey The price id or lookup key.
     *
     * @return Price Teh matching price.
     */
    public function get(string $idOrLookupKey): Price
    {
        return $this->all()->first(static function (Price $price) use ($idOrLookupKey) {
            return $price->id === $idOrLookupKey || $price->lookup_key === $idOrLookupKey;
        });
    }
}

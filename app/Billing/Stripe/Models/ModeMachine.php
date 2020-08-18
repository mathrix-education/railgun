<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use function array_keys;
use function collect;

/**
 * Stripe definitions for the "Mode Machine" premium plan.
 */
class ModeMachine
{
    public const PLAN_MONTH    = 'laser_month';
    public const PLAN_QUARTER  = 'laser_quarter';
    public const PLAN_SEMESTER = 'laser_semester';
    public const PLAN_YEAR     = 'laser_year';
    public const PLANS         = [
        self::PLAN_MONTH,
        self::PLAN_QUARTER,
        self::PLAN_SEMESTER,
        self::PLAN_YEAR,
    ];
    public const PRODUCT       = [
        'id'          => 'mode_machine',
        'name'        => 'Mode Machine',
        'active'      => true,
        'description' => 'Accès au contenu premium de Mathrix.fr en illimité.',
    ];
    public const PRICES        = [
        self::PLAN_MONTH    => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_MONTH,
            'nickname'    => 'Plan mensuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 2700,
        ],
        self::PLAN_QUARTER  => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_QUARTER,
            'nickname'    => 'Plan trimestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 3,
            ],
            'unit_amount' => 6700,
        ],
        self::PLAN_SEMESTER => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_SEMESTER,
            'nickname'    => 'Plan semestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 6,
            ],
            'unit_amount' => 9700,
        ],
        self::PLAN_YEAR     => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_YEAR,
            'nickname'    => 'Plan annuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 16700,
        ],
    ];

    protected StripeClient $client;

    public function __construct(StripeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return Collection|Price[] The "Mode Machine" prices.
     *
     * @throws ApiErrorException
     */
    public function all(): Collection
    {
        return Cache::tags('stripe')->remember('plans', 3600, function () {
            return collect(
                $this->client
                    ->prices
                    ->all([
                        'lookup_keys' => array_keys(self::PRICES),
                        'expand'      => ['data.product'],
                    ])
                    ->data,
            );
        });
    }

    /**
     * @param string $idOrLookupKey The price id or lookup key.
     *
     * @return Price The matching price.
     *
     * @throws ApiErrorException
     */
    public function get(string $idOrLookupKey): Price
    {
        return $this->all()->first(static function (Price $price) use ($idOrLookupKey) {
            return $price->id === $idOrLookupKey || $price->lookup_key === $idOrLookupKey;
        });
    }
}

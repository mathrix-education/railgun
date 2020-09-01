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
    public const PLAN_MONTH             = 'laser_month';
    public const PLAN_QUARTER           = 'laser_quarter';
    public const PLAN_SEMESTER          = 'laser_semester';
    public const PLAN_YEAR              = 'laser_year';
    public const PLAN_LEGACY_MONTHLY    = 'legacy_monthly';
    public const PLAN_LEGACY_ANNUALLY   = 'legacy_annually';
    public const PLAN_LEGACY_BIANNUALLY = 'legacy_biannually';
    public const PLANS                  = [
        self::PLAN_MONTH,
        self::PLAN_QUARTER,
        self::PLAN_SEMESTER,
        self::PLAN_YEAR,
    ];
    public const PRODUCT                = [
        'id'          => 'mode_machine',
        'name'        => 'Mode Machine',
        'active'      => true,
        'description' => 'Accès au contenu premium de Mathrix.fr en illimité.',
    ];
    public const PRICES                 = [
        self::PLAN_MONTH             => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_MONTH,
            'nickname'    => 'Plan mensuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 2790,
        ],
        self::PLAN_QUARTER           => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_QUARTER,
            'nickname'    => 'Plan trimestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 3,
            ],
            'unit_amount' => 6870,
        ],
        self::PLAN_SEMESTER          => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_SEMESTER,
            'nickname'    => 'Plan semestriel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 6,
            ],
            'unit_amount' => 10140,
        ],
        self::PLAN_YEAR              => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_YEAR,
            'nickname'    => 'Plan annuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 16680,
        ],
        self::PLAN_LEGACY_MONTHLY    => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_LEGACY_MONTHLY,
            'nickname'    => 'Plan mensuel (legacy)',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 1790,
        ],
        self::PLAN_LEGACY_ANNUALLY   => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_LEGACY_ANNUALLY,
            'nickname'    => 'Plan annuel (legacy)',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 15480,
        ],
        self::PLAN_LEGACY_BIANNUALLY => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_LEGACY_BIANNUALLY,
            'nickname'    => 'Plan biannuel (legacy)',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 11880,
        ],
    ];
    public const PLAN_MAPPINGS          = [
        'v3_monthly'    => ModeMachine::PLAN_LEGACY_MONTHLY,
        'v3_annually'   => ModeMachine::PLAN_LEGACY_ANNUALLY,
        'v3_biannually' => ModeMachine::PLAN_LEGACY_BIANNUALLY,
        'v4_monthly'    => ModeMachine::PLAN_LEGACY_MONTHLY,
        'v4_annually'   => ModeMachine::PLAN_LEGACY_ANNUALLY,
        'v4_biannually' => ModeMachine::PLAN_LEGACY_BIANNUALLY,
        'v5_monthly'    => ModeMachine::PLAN_LEGACY_MONTHLY,
        'v5_annually'   => ModeMachine::PLAN_LEGACY_ANNUALLY,
        'v5_biannually' => ModeMachine::PLAN_LEGACY_BIANNUALLY,
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
        $idOrLookupKey = self::PLAN_MAPPINGS[$idOrLookupKey] ?? $idOrLookupKey;

        return $this->all()->first(static function (Price $price) use ($idOrLookupKey) {
            return $price->id === $idOrLookupKey || $price->lookup_key === $idOrLookupKey;
        });
    }
}

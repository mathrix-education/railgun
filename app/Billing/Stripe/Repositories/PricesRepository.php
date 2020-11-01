<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use function array_keys;
use function collect;

class PricesRepository
{
    public const PREMIUM_MONTH             = 'laser_month';
    public const PREMIUM_QUARTER           = 'laser_quarter';
    public const PREMIUM_SEMESTER          = 'laser_semester';
    public const PREMIUM_YEAR              = 'laser_year';
    public const PREMIUM_LEGACY_MONTHLY    = 'legacy_monthly';
    public const PREMIUM_LEGACY_ANNUALLY   = 'legacy_annually';
    public const PREMIUM_LEGACY_BIANNUALLY = 'legacy_biannually';
    public const ACADEMY_MONTH             = 'academy_month';
    public const ACADEMY_QUARTER           = 'academy_quarter';
    public const ACADEMY_SEMESTER          = 'academy_semester';
    public const ACADEMY_YEAR              = 'academy_year';
    public const PREMIUM_PLANS             = [
        self::PREMIUM_MONTH,
        self::PREMIUM_QUARTER,
        self::PREMIUM_SEMESTER,
        self::PREMIUM_YEAR,
    ];
    public const PREMIUM_LEGACY_PLANS      = [
        self::PREMIUM_LEGACY_MONTHLY,
        self::PREMIUM_LEGACY_ANNUALLY,
        self::PREMIUM_LEGACY_BIANNUALLY,
    ];
    public const ACADEMY_PLANS             = [
        self::ACADEMY_MONTH,
        self::ACADEMY_QUARTER,
        self::ACADEMY_SEMESTER,
        self::ACADEMY_YEAR,
    ];
    public const MAPPINGS                  = [
        'v3_monthly'    => self::PREMIUM_LEGACY_MONTHLY,
        'v3_annually'   => self::PREMIUM_LEGACY_ANNUALLY,
        'v3_biannually' => self::PREMIUM_LEGACY_BIANNUALLY,
        'v4_monthly'    => self::PREMIUM_LEGACY_MONTHLY,
        'v4_annually'   => self::PREMIUM_LEGACY_ANNUALLY,
        'v4_biannually' => self::PREMIUM_LEGACY_BIANNUALLY,
        'v5_monthly'    => self::PREMIUM_LEGACY_MONTHLY,
        'v5_annually'   => self::PREMIUM_LEGACY_ANNUALLY,
        'v5_biannually' => self::PREMIUM_LEGACY_BIANNUALLY,
    ];
    public const DATA = [
        // Mode machine
        self::PREMIUM_MONTH             => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_MONTH,
            'nickname'    => 'Plan mensuel',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 2790,
        ],
        self::PREMIUM_QUARTER           => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_QUARTER,
            'nickname'    => 'Plan trimestriel',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 3,
            ],
            'unit_amount' => 6870,
        ],
        self::PREMIUM_SEMESTER          => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_SEMESTER,
            'nickname'    => 'Plan semestriel',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 6,
            ],
            'unit_amount' => 10140,
        ],
        self::PREMIUM_YEAR              => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_YEAR,
            'nickname'    => 'Plan annuel',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 16680,
        ],
        self::PREMIUM_LEGACY_MONTHLY    => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_LEGACY_MONTHLY,
            'nickname'    => 'Plan mensuel (legacy)',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 1790,
        ],
        self::PREMIUM_LEGACY_ANNUALLY   => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_LEGACY_ANNUALLY,
            'nickname'    => 'Plan annuel (legacy)',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 15480,
        ],
        self::PREMIUM_LEGACY_BIANNUALLY => [
            'currency'    => 'eur',
            'lookup_key'  => self::PREMIUM_LEGACY_BIANNUALLY,
            'nickname'    => 'Plan biannuel (legacy)',
            'product'     => ProductsRepository::MODE_MACHINE,
            'recurring'   => [
                'interval'       => 'year',
                'interval_count' => 1,
            ],
            'unit_amount' => 11880,
        ],
        // Academy
        self::ACADEMY_MONTH             => [
            'currency'    => 'eur',
            'lookup_key'  => self::ACADEMY_MONTH,
            'nickname'    => 'Plan mensuel',
            'product'     => ProductsRepository::ACADEMY,
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 2990,
        ],
        //self::ACADEMY_QUARTER           => [
        //    'currency'    => 'eur',
        //    'lookup_key'  => self::ACADEMY_QUARTER,
        //    'nickname'    => 'Plan trimestriel',
        //    'product'     => ProductsRepository::ACADEMY,
        //    'recurring'   => [
        //        'interval'       => 'month',
        //        'interval_count' => 3,
        //    ],
        //    'unit_amount' => 4990,
        //],
        //self::ACADEMY_SEMESTER          => [
        //    'currency'    => 'eur',
        //    'lookup_key'  => self::ACADEMY_SEMESTER,
        //    'nickname'    => 'Plan semestriel',
        //    'product'     => ProductsRepository::ACADEMY,
        //    'recurring'   => [
        //        'interval'       => 'month',
        //        'interval_count' => 6,
        //    ],
        //    'unit_amount' => 9590,
        //],
        //self::ACADEMY_YEAR              => [
        //    'currency'    => 'eur',
        //    'lookup_key'  => self::ACADEMY_YEAR,
        //    'nickname'    => 'Plan annuel',
        //    'product'     => ProductsRepository::ACADEMY,
        //    'recurring'   => [
        //        'interval'       => 'year',
        //        'interval_count' => 1,
        //    ],
        //    'unit_amount' => 15990,
        //],
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
        return Cache::tags('stripe')->remember('prices', 3600, function () {
            return collect(array_keys(self::DATA))
                ->chunk(10) // Stripe requests are limited to 10 lookup keys
                ->flatMap(function (Collection $keys) {
                    return $this->client
                        ->prices
                        ->all([
                            'lookup_keys' => $keys->values()->toArray(),
                            'expand'      => ['data.product'],
                        ])
                        ->data;
                });
        });
    }

    /**
     * @param string $idOrLookupKey The price id or lookup key.
     *
     * @return Price The matching price.
     *
     * @throws ApiErrorException
     */
    public function get(string $idOrLookupKey): ?Price
    {
        $idOrLookupKey = self::MAPPINGS[$idOrLookupKey] ?? $idOrLookupKey;

        $found = $this->all()->first(static function (Price $price) use ($idOrLookupKey) {
            return $price->id === $idOrLookupKey || $price->lookup_key === $idOrLookupKey;
        });

        if (!$found) {
            throw new InvalidArgumentException("Unable to find price with id $idOrLookupKey");
        }

        return $found;
    }
}

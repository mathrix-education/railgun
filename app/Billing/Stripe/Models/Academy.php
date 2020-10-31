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

class Academy
{
    public const PLAN_MONTH    = 'academy_month';
    public const PLAN_QUARTER  = 'academy_quarter';
    public const PLAN_SEMESTER = 'academy_semester';
    public const PLAN_YEAR     = 'academy_year';
    public const PLANS         = [
        self::PLAN_MONTH,
    ];
    public const PRODUCT       = [
        'id'          => 'academy',
        'name'        => 'Academy',
        'active'      => true,
        'description' => 'Accès au contenu des lives.',
    ];
    public const PRICES        = [
        self::PLAN_MONTH => [
            'currency'    => 'eur',
            'lookup_key'  => self::PLAN_MONTH,
            'nickname'    => 'Plan mensuel',
            'product'     => self::PRODUCT['id'],
            'recurring'   => [
                'interval'       => 'month',
                'interval_count' => 1,
            ],
            'unit_amount' => 1990,
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
            'unit_amount' => 4990,
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
            'unit_amount' => 9590,
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
            'unit_amount' => 15990,
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
        return Cache::tags('stripe')->remember('academy.plans', 3600, function () {
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
    public function get(string $idOrLookupKey): ?Price
    {
        return $this->all()->first(static function (Price $price) use ($idOrLookupKey) {
            return $price->id === $idOrLookupKey || $price->lookup_key === $idOrLookupKey;
        });
    }
}

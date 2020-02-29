<?php

declare(strict_types=1);

namespace Railgun\Billing\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use function collect;
use function round;

class Plan
{
    public const MONTHLY_PLAN    = 'v5_monthly';
    public const ANNUALLY_PLAN   = 'v5_annually';
    public const BIANNUALLY_PLAN = 'v5_biannually';

    public const PLANS = [self::MONTHLY_PLAN, self::ANNUALLY_PLAN, self::BIANNUALLY_PLAN];

    public const PLANS_DETAILS = [
        self::MONTHLY_PLAN    => [
            'id'               => self::MONTHLY_PLAN,
            'period'           => 'month',
            'price_per_period' => 1790,
            'price_per_month'  => 1790,
            'prorated'         => false,
        ],
        self::ANNUALLY_PLAN   => [
            'id'               => self::ANNUALLY_PLAN,
            'period'           => 'year',
            'price_per_period' => 15480,
            'price_per_month'  => 1290,
            'prorated'         => true,
        ],
        self::BIANNUALLY_PLAN => [
            'id'               => self::BIANNUALLY_PLAN,
            'period'           => 'year',
            'price_per_period' => 11880,
            'price_per_month'  => 990,
            'prorated'         => true,
        ],
    ];

    public string  $id;
    public string  $period;
    public int     $price_per_period;
    public int     $price_per_month;
    public bool    $prorated;
    public ?int    $proration_price  = null;
    public ?Carbon $proration_anchor = null;

    public function __construct(string $id)
    {
        if (!isset(self::PLANS_DETAILS[$id])) {
            throw new ModelNotFoundException(self::class, $id);
        }

        $details                = self::PLANS_DETAILS[$id];
        $this->id               = $details['id'];
        $this->period           = $details['period'];
        $this->price_per_period = $details['price_per_period'];
        $this->price_per_month  = $details['price_per_month'];
        $this->prorated         = $details['prorated'];

        if (!$this->prorated) {
            return;
        }

        $this->proration_price  = self::getProrationPrice($this->id);
        $this->proration_anchor = self::getProrationAnchor($this->id);
    }

    /**
     * @return Collection|Plan[]
     */
    public static function all()
    {
        return collect(self::PLANS)->map(fn(string $planId) => new self($planId));
    }

    /**
     * Get the proration price. If the plan is not prorated, return null.
     * Like Stripe, this function has be second-precise.
     *
     * @see https://stripe.com/docs/billing/subscriptions/prorations
     *
     * @param string $planId The plan id.
     *
     * @return int|null The proration price in cents.
     */
    public static function getProrationPrice(string $planId): ?int
    {
        if (!self::isPlanProrated($planId)) {
            return null;
        }

        // We use anchor to avoid issues with leap years.
        $nextAnchor = self::getProrationAnchor($planId);
        $previous   = $nextAnchor->clone()->subYear();

        // Absolute
        $periodLengthInSeconds = $nextAnchor->diffInRealSeconds($previous);
        $secondsUntilAnchor    = $nextAnchor->diffInRealSeconds(Carbon::now());

        $periodPrice = self::PLANS_DETAILS[$planId]['price_per_period'];

        return (int)round($periodPrice * ($secondsUntilAnchor / $periodLengthInSeconds));
    }

    /**
     * Check if the plan is prorated.
     *
     * @param string $planId The plan id.
     *
     * @return bool If the plan is prorated.
     */
    public static function isPlanProrated(string $planId): bool
    {
        return self::PLANS_DETAILS[$planId]['prorated'];
    }

    /**
     * Get the plan next "summer" anchor.
     *
     * @param string $planId The plan id.
     *
     * @return Carbon The proration anchor.
     */
    public static function getProrationAnchor(string $planId): Carbon
    {
        if (!self::isPlanProrated($planId)) {
            throw new InvalidArgumentException("Plan $planId is not proratable");
        }

        $nextThirtyFirstJuly = Carbon::now()
            ->setMonth(7)
            ->setDay(31)
            ->setTime(0, 0, 0);

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        return $nextThirtyFirstJuly;
    }
}

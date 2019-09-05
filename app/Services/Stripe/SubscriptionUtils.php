<?php

namespace App\Services\Stripe;

use Carbon\Carbon;

/**
 * Class SubscriptionUtils.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SubscriptionUtils
{
    public const MONTHLY_PLAN = "v5_monthly";
    public const ANNUALLY_PLAN = "v5_annually";

    public const PLANS = [self::MONTHLY_PLAN, self::ANNUALLY_PLAN];

    public const PLANS_DETAILS = [
        self::MONTHLY_PLAN => [
            "id" => self::MONTHLY_PLAN,
            "prorated" => true,
            "period" => "month",
            "price_per_period" => 1790,
            "price_per_month" => 1790
        ],
        self::ANNUALLY_PLAN => [
            "id" => self::ANNUALLY_PLAN,
            "prorated" => false,
            "period" => "year",
            "price_per_period" => 15480,
            "price_per_month" => 1290
        ]
    ];


    /**
     * Check if the plan is prorated.
     *
     * @param string $planId The plan id.
     *
     * @return bool
     */
    public static function isPlanProrated(string $planId): bool
    {
        return self::PLANS_DETAILS[$planId]["prorated"];
    }


    /**
     * Get the plan next "summer" anchor. If the plan is not prorated, return null.
     *
     * @param string $planId The plan id.
     *
     * @return Carbon
     */
    public static function getProrationAnchor(string $planId): ?Carbon
    {
        if (!self::isPlanProrated($planId)) {
            return null;
        }

        $nextThirtyFirstJuly = Carbon::now()
            ->setMonth(7)->setDay(31)
            ->setTime(0, 0, 0);

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        return $nextThirtyFirstJuly;
    }


    /**
     * Get the proration price. If the plan is not prorated, return null.
     * Like Stripe, this function has be second-precise.
     *
     * @see https://stripe.com/docs/billing/subscriptions/prorations
     *
     * @param string $planId The plan id.
     *
     * @return int
     */
    public static function getProrationPrice(string $planId): ?int
    {
        if (!self::isPlanProrated($planId)) {
            return null;
        }

        // We use anchor to avoid issues with leap years.
        $nextAnchor = self::getProrationAnchor($planId);
        $previous = $nextAnchor->clone()->subYear();

        // Absolute
        $periodLengthInSeconds = $nextAnchor->diffInRealSeconds($previous);
        $secondsUntilAnchor = $nextAnchor->diffInRealSeconds(now());

        $periodPrice = self::PLANS_DETAILS[self::ANNUALLY_PLAN]["price_per_period"];

        return round($periodPrice * ($secondsUntilAnchor / $periodLengthInSeconds));
    }
}

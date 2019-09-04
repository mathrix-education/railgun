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
    public const BIANNUALLY_PLAN = "v5_biannually";

    public const PLANS = [self::MONTHLY_PLAN, self::ANNUALLY_PLAN, self::BIANNUALLY_PLAN];

    public const PLANS_DETAILS = [
        self::MONTHLY_PLAN => [
            "id" => self::MONTHLY_PLAN,
            "regular" => true,
            "period" => "month",
            "price_per_period" => 1790,
            "price_per_month" => 1790
        ],
        self::ANNUALLY_PLAN => [
            "id" => self::ANNUALLY_PLAN,
            "regular" => false,
            "period" => "year",
            "price_per_period" => 15480,
            "price_per_month" => 1290
        ],
        self::BIANNUALLY_PLAN => [
            "id" => self::BIANNUALLY_PLAN,
            "regular" => false,
            "period" => "year",
            "price_per_period" => 11880,
            "price_per_month" => 990
        ],
    ];


    /**
     * Check if the given plan id is a "regular plan".
     *
     * @param string $planId The plan id.
     *
     * @return bool
     */
    public static function isRegularPlan(string $planId): bool
    {
        return self::PLANS_DETAILS[$planId]["regular"];
    }


    /**
     * Check if the given plan id is a "Trial plan".
     *
     * @param string $planId The plan id to check.
     *
     * @return bool
     */
    public static function isTrialPlan(string $planId): bool
    {
        return !self::isRegularPlan($planId);
    }


    /**
     * Get the plan start date (after trial).
     *
     * @param string $planId The Braintree plan.
     *
     * @return Carbon
     */
    public static function getTrialEndDate(string $planId): ?Carbon
    {
        if (self::isRegularPlan($planId)) {
            return null;
        }

        $nextThirtyFirstJuly = Carbon::now()->setMonth(7)->setDay(31)->setTime(0, 0, 0);

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        if ($planId === self::BIANNUALLY_PLAN) {
            $nextThirtyFirstJuly->addYear();
        }

        return $nextThirtyFirstJuly;
    }


    /**
     * Get trial duration in days.
     *
     * @param string $planId The plan id.
     *
     * @return int
     */
    public static function getTrialDuration(string $planId): ?int
    {
        if (self::isRegularPlan($planId)) {
            return null;
        }

        return self::getTrialEndDate($planId)->diffInDays(Carbon::now());
    }


    /**
     * Get the trial period price, which is the price for the period between now and the regular plan start date.
     *
     * @param string $planId The Braintree plan.
     *
     * @return int The price in cents (€).
     */
    public static function getTrialPeriodPrice(string $planId): ?int
    {
        if (self::isRegularPlan($planId)) {
            return null;
        }

        $now = Carbon::now();
        $trialEndDate = self::getTrialEndDate($planId);

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round(
            $diffInMonths * self::PLANS_DETAILS[$planId]["price_per_month"]
            + $diffInDays * (self::PLANS_DETAILS[$planId]["price_per_month"] / 30),
            0);
    }
}

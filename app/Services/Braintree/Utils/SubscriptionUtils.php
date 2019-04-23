<?php

namespace App\Services\Braintree\Utils;

use Carbon\Carbon;

/**
 * Class SubscriptionUtils.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SubscriptionUtils
{
    public const PRICE_PER_MONTH = [
        "v4_monthly" => 17.90,
        "v4_annually" => 12.90,
        "v4_biannually" => 9.90,
    ];
    private static $EnableFake = false;
    public static $ForceTrialPrice = null;


    /**
     * Enable fake system.
     *
     * @param bool $enable
     */
    public static function fake($enable = true)
    {
        self::$EnableFake = $enable;
    }


    /**
     * Get the plan start date (after trial).
     *
     * @param string $plan The Braintree plan.
     *
     * @return Carbon
     */
    public static function getRegularPlanStartDate(string $plan)
    {
        $nextThirtyFirstJuly = new Carbon("31st July");

        if ($nextThirtyFirstJuly->isPast() || (new Carbon("1st May"))->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        if ($plan === "v4_biannually") {
            $nextThirtyFirstJuly->addYear();
        }

        return $nextThirtyFirstJuly;
    }


    /**
     * Get the trial period price, which is the price for the period between now and the regular plan start date.
     *
     * @param string $plan The Braintree plan.
     *
     * @return float|int The price in euros (€).
     */
    public static function getTrialPeriodPrice(string $plan)
    {
        // Used to mock trial price
        if (env("BRAINTREE_ENVIRONMENT") === "sandbox" && self::$EnableFake && is_numeric(self::$ForceTrialPrice)) {
            return self::$ForceTrialPrice;
        }

        $now = Carbon::now();
        $trialEndDate = self::getRegularPlanStartDate($plan);

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round(
            $diffInMonths * self::PRICE_PER_MONTH[$plan]
            + $diffInDays * (self::PRICE_PER_MONTH[$plan] / 30),
            2);
    }
}

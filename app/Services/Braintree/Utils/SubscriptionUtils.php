<?php

declare(strict_types=1);

namespace App\Services\Braintree\Utils;

use Braintree\CustomerSearch;
use Braintree\Subscription;
use Carbon\Carbon;
use Throwable;
use function env;
use function gateway;
use function in_array;
use function is_numeric;
use function round;

class SubscriptionUtils
{
    // Plans
    public const MONTHLY_PLAN      = 'v4_monthly';
    public const ANNUALLY_PLAN     = 'v4_annually';
    public const BIANNUALLY_PLAN   = 'v4_biannually';
    public const PLANS             = [self::MONTHLY_PLAN, self::ANNUALLY_PLAN, self::BIANNUALLY_PLAN];
    public const REGULAR_PLANS     = [self::MONTHLY_PLAN];
    public const TRIAL_PLANS       = [self::ANNUALLY_PLAN, self::BIANNUALLY_PLAN];
    public const PRICE_PER_MONTH   = [
        self::MONTHLY_PLAN => 17.90,
        self::ANNUALLY_PLAN => 12.90,
        self::BIANNUALLY_PLAN => 9.90,
    ];
    public static $ForceTrialPrice = null;
    private static $EnableFake     = false;

    /**
     * Enable fake system.
     */
    public static function fake(bool $enable = true) : void
    {
        self::$EnableFake = $enable;
    }

    /**
     * Check if the given plan id is a "Trial plan".
     *
     * @param string $planId The plan id to check.
     */
    public static function isTrialPlan(string $planId) : bool
    {
        return in_array($planId, self::TRIAL_PLANS);
    }

    /**
     * Get trial duration in days.
     *
     * @param string $planId The plan id.
     */
    public static function getTrialDuration(string $planId) : int
    {
        return self::getTrialEndDate($planId)->diffInDays(Carbon::now());
    }

    /**
     * Get the plan start date (after trial).
     *
     * @param string $planId The Braintree plan.
     */
    public static function getTrialEndDate(string $planId) : Carbon
    {
        try {
            $nextThirtyFirstJuly = new Carbon('31st July');

            if ($nextThirtyFirstJuly->isPast() || (new Carbon('1st May'))->isPast()) {
                $nextThirtyFirstJuly->addYear();
            }

            if ($planId === 'v4_biannually') {
                $nextThirtyFirstJuly->addYear();
            }

            return $nextThirtyFirstJuly;
        } catch (Throwable $e) {
            // TODO: Silent error
        }
    }

    /**
     * Get the trial period price, which is the price for the period between now and the regular plan start date.
     *
     * @param string $planId The Braintree plan.
     *
     * @return float The price in euros (€).
     */
    public static function getTrialPeriodPrice(string $planId) : float
    {
        // Used to mock trial price
        if (env('BRAINTREE_ENVIRONMENT') === 'sandbox'
            && self::$EnableFake
            && is_numeric(self::$ForceTrialPrice)
        ) {
            return self::$ForceTrialPrice;
        }

        $now          = Carbon::now();
        $trialEndDate = self::getTrialEndDate($planId);

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays   = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round(
            $diffInMonths * self::PRICE_PER_MONTH[$planId]
            + $diffInDays * (self::PRICE_PER_MONTH[$planId] / 30),
            2
        );
    }

    public static function getAssociatedCustomerId(Subscription $subscription) : ?string
    {
        $results = gateway()->customer()->search([CustomerSearch::paymentMethodToken()->is($subscription->paymentMethodToken)]);
        $ids     = $results->getIds();

        if (! empty($ids)) {
            return $ids[0];
        }

        return null;
    }
}

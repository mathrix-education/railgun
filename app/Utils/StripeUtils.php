<?php
namespace App\Utils;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Training;
use Carbon\Carbon;

/**
 * Class StripeUtils
 *
 * Set of utilities for STripe price calculations
 *
 * @package App\Utils
 */
class StripeUtils
{
    public const ALLOWED_STRIPE_PLANS = [
        "v3_biannually" => [
            "name" => "Pack bi-annuel",
            "price_per_period" => 23760,
            "price_per_month" => 9.90,
            "price_per_day" => 0.33,
            "interval" => "year",
            "interval_count" => 2
        ],
        "v3_annually" => [
            "name" => "Pack annuel",
            "price_per_period" => 15480,
            "price_per_month" => 12.90,
            "price_per_day" => 0.43,
            "interval" => "year",
            "interval_count" => 2
        ],
        "v3_monthly" => [
            "name" => "Pack mensuel",
            "price_per_period" => 1790,
            "price_per_month" => 17.90,
            "price_per_day" => 0.60,
            "interval" => "monthly",
            "interval_count" => 1
        ]
    ];

    public const SPONSORSHIP_REFUND = 1000;
    public const SPONSORSHIP_DISCOUNT = 1000;
    public const SECTION_PRICE = 700;


    /**
     * Get the end date for a given Stripe plan
     *
     * @param string $stripePlan
     *
     * @return Carbon
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public static function getStripePlanEndDate(string $stripePlan): Carbon
    {
        // Calculate the next July (ie. the ending date)
        $nextJuly = Carbon::create(null, 7, 31);
        $firstMay = Carbon::create(null, 5, 1);

        switch ($stripePlan) {
            case "v3_biannually":
                if ($firstMay->isFuture()) {
                    // January, 1st to April, 30th
                    $nextJuly->addYears(1);
                } else {
                    // May, 1st to December, 31st
                    $nextJuly->addYears(2);
                }
                break;
            case "v3_annually":
                if ($firstMay->isFuture()) {
                    // January, 1st to April, 30th
                    // Do nothing
                } else {
                    // May, 1st to December, 31st
                    $nextJuly->addYears(1);
                }
                break;
            default:
                throw new Http400InvalidRequestException("$stripePlan is not a valid estimable plan");
                break;
        }

        return $nextJuly->setTime(0, 0, 0);
    }


    /**
     * Get the price of a Stripe plan based on the current date
     *
     * @param string $stripePlan
     *
     * @return int the cost in cents
     * @throws Http400InvalidRequestException
     */
    public static function getStripePlanPrice(string $stripePlan): int
    {
        $firstMay = Carbon::create(null, 5, 1);
        $firstAugust = Carbon::create(null, 8, 1);

        // Calculate the next July (ie. the ending date)

        $now = Carbon::now();
        $nextJuly = self::getStripePlanEndDate($stripePlan);

        $months = $nextJuly->diffInMonths($now);
        $days = $nextJuly->diffInDays($now->addMonths($months));

        if ($firstMay->isPast() && $firstAugust->isFuture()) {
            $months -= 2;
        }

        $pricePerMonth = self::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_month"];
        $pricePerDay = self::ALLOWED_STRIPE_PLANS[$stripePlan]["price_per_day"];

        return round($pricePerMonth * $months + $pricePerDay * $days, 1) * 100;
    }


    /**
     * Get the surcharge price for the premium offer with a formation
     *
     * @param Training|null $training
     *
     * @return int the surcharge in cents
     */
    public static function getSurchargeForPremium(Training $training = null): int
    {
        return 1000;
    }


    /**
     * Get the next July, 31st (in the future)
     *
     * @return Carbon
     */
    public static function getNextJuly()
    {
        $nextJuly = Carbon::create(null, 7, 31);

        if ($nextJuly->isPast()) {
            $nextJuly->addYear();
        }

        $nextJuly->setTime(0, 0, 0);

        return $nextJuly;
    }
}

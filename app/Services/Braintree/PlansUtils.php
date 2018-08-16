<?php
namespace App\Services\Braintree;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class TrialUtils
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class PlansUtils
{
    /**
     * Get the formatted plans with additional fields
     *
     * @return mixed
     */
    public static function retrievePlans()
    {
        return Cache::remember("braintree-plans", 0, function () {
            $plansData = Formatter::cast(\Braintree_Plan::all());

            foreach ($plansData as $k => $plan) {
                $plan["pricePerMonth"] = round($plan["price"] / $plan["billingFrequency"], 2);
            }

            usort($plansData, function ($a, $b) {
                return $b["pricePerMonth"] <=> $a["pricePerMonth"];
            });

            $highestPricePerMonth = round($plansData[0]["price"] / $plansData[0]["billingFrequency"], 2);
            foreach ($plansData as $k => $plan) {
                $plan["discount"] = 100 - round($plan["pricePerMonth"] * 100 / $highestPricePerMonth, 0);

                $hasTrial = Str::contains($plan["id"], "annually");

                $plan["freeHolidays"] = $hasTrial;


                if ($hasTrial) {
                    $plan["trialPrice"] = self::getTrialPrice($plan);
                    $plan["trialPeriod"] = true;
                    $trialStartDate = Carbon::now();
                    $trialEndDate = self::getTrialEndDate($plan);
                    $plan["trialStartDate"] = $trialStartDate->__toString();
                    $plan["trialEndDate"] = $trialEndDate->__toString();

                    $pricePerYear = self::toPrice($plan["price"]);
                    $pricePerMonth = self::toPrice($plan["pricePerMonth"]);
                    $trialPrice = self::toPrice($plan["trialPrice"]);
                    $trialStartDate = $trialStartDate->format("d/m/y");
                    $trialEndDate = $trialEndDate->format("d/m/y");


                    // Build description.
                    $plan["description"] = "Facturée en un paiment unique de $trialPrice pour la période du " .
                        "$trialStartDate au $trialEndDate.";

                    // Build price details
                    $plan["pricingDetails"] = "Pour la formule {$plan["name"]} du $trialStartDate " .
                        "au $trialEndDate, le prix de $trialPrice est égal au prorata de l'année scolaire restante, " .
                        "calculé sur la base de $pricePerMonth par mois. L'année scolaire suivante sera facturée " .
                        "soit $pricePerYear multiplié par 12 mois";
                } else {
                    $plan["trialStartDate"] = null;
                    $plan["trialEndDate"] = null;

                    // Build description
                    $plan["description"] = "Formule renouvelée tous les mois.";

                    // Build pricing details
                    $plan["pricingDetails"] = "L’abonnement est renouvelé automatiquement en fin " .
                        "de période. Vous pouvez résilier votre abonnement à tout moment depuis votre compte.";
                }

                ksort($plan);

                $plansData[$k] = $plan;
            }

            return $plansData;
        });
    }


    /**
     * Get the start date for a given plan
     *
     * @param array $plan
     *
     * @return Carbon
     */
    public static function getTrialEndDate(array $plan)
    {
        $nextThirtyFirstJuly = new Carbon("31st July");

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        } else if ((new Carbon("1st May"))->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        switch ($plan["id"]) {
            case "v4_annually":
                break;
            case "v4_biannually":
                $nextThirtyFirstJuly->addYear();
                break;
            default:
        }

        return $nextThirtyFirstJuly;
    }


    /**
     * @param array $plan
     *
     * @return float|int
     */
    public static function getTrialPrice(array $plan)
    {
        $now = Carbon::now();
        $trialEndDate = self::getTrialEndDate($plan);

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round($diffInMonths * $plan["pricePerMonth"] + $diffInDays * ($plan["pricePerMonth"] / 30), 2);
    }


    /**
     * Convert a float to a well formatted price
     *
     * @param float $price
     * @param string $currencySymbol
     *
     * @return string
     */
    public static function toPrice(float $price, $currencySymbol = "€")
    {
        $price *= 100;
        $priceStr = "$price";
        return substr($priceStr, 0, -2) . "." . substr($priceStr, -2) . " $currencySymbol";
    }
}

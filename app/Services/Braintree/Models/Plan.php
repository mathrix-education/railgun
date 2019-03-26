<?php

namespace App\Services\Braintree\Models;

use Braintree\Discount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class Plan.
 * Implements the Braintree Plan object and add a few more details.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Services\Braintree\Models
 * @mixin     \Braintree\Plan
 *
 * @property int $billingDayOfMonth
 * @property int $billingFrequency
 * @property string $currencyIsoCode
 * @property string $description
 * @property string $id
 * @property string $name
 * @property float $price
 * @property string $trialDuration
 * @property string $trialDurationUnit
 * @property bool $trialPeriod
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $pricingDetails
 * @property int $pricePerMonth
 * @property int $discount
 * @property bool $freeHolidays
 * @property float|null $trialPrice
 * @property Carbon|null $trialStartDate
 * @property Carbon|null $trialEndDate
 * @property Discount[] $discounts
 */
class Plan extends BraintreeModel
{
    public static $highestPricePerMonth = null;


    public function __construct(\Braintree\Plan $plan)
    {
        parent::__construct($plan);

        $this->price = (float)($this->price);
        $this->trialPeriod = Str::contains($this->id, "annually");

        $this->freeHolidays = $this->trialPeriod;
        $this->pricePerMonth = round($this->price / $this->billingFrequency, 2);

        if (!$this->trialPeriod) {
            $this->trialPrice = null;
            $this->trialStartDate = null;
            $this->trialEndDate = null;

            // Details
            $this->description = "Formule renouvelée tous les mois.";
            $this->pricingDetails = "L’abonnement est renouvelé automatiquement en fin de période. Vous pouvez "
                . "résilier votre abonnement à tout moment depuis votre compte.";
        } else {
            $this->trialPrice = $this->getTrialPrice();
            $this->trialStartDate = Carbon::now();
            $this->trialEndDate = $this->getTrialEndDate();

            // Details
            $pricePerYear = $this->formatPrice($this->price);
            $pricePerMonth = $this->formatPrice($this->pricePerMonth);
            $trialPrice = $this->formatPrice($this->trialPrice);
            $trialStartDate = $this->trialStartDate->format("d/m/y");
            $trialEndDate = $this->trialEndDate->format("d/m/y");

            $this->description = "Facturée en un paiment unique de $trialPrice pour la période du $trialStartDate au "
                . "$trialEndDate.";
            $this->pricingDetails = "Pour la formule {$this->name} du $trialStartDate au $trialEndDate, le prix de "
                . "$trialPrice est égal au prorata de l'année scolaire restante, calculé sur la base de $pricePerMonth "
                . "par mois. L'année scolaire suivante sera facturée soit $pricePerYear multiplié par 12 mois";
        }

        if (self::$highestPricePerMonth !== null) {
            $this->discount = 100 - round($this->pricePerMonth * 100 / self::$highestPricePerMonth, 0);
        } else {
            $this->discount = null;
        }
    }


    /**
     * Get the trial price in euros.
     *
     * @return float
     */
    public function getTrialPrice()
    {
        $now = Carbon::now();
        $trialEndDate = $this->getTrialEndDate();

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round($diffInMonths * $this->pricePerMonth + $diffInDays * ($this->pricePerMonth / 30), 2);
    }


    /**
     * Get the plan trial end date.
     *
     * @return Carbon|null The plan trial en date, null if the plan has no trial
     */
    public function getTrialEndDate(): Carbon
    {
        $nextThirtyFirstJuly = new Carbon("31st July");

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        } else if ((new Carbon("1st May"))->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        switch ($this->id) {
            case "v4_annually":
                break;
            case "v4_biannually":
                $nextThirtyFirstJuly->addYear();
                break;
            default:
                return null;
        }

        return $nextThirtyFirstJuly;
    }


    /**
     * Convert a float to a well-formatted price.
     *
     * @param float $price
     * @param string $currencySymbol
     *
     * @return string
     */
    public function formatPrice($price, $currencySymbol = "€")
    {
        $price *= 100;
        $priceStr = "$price";

        return mb_substr($priceStr, 0, -2) . "." . mb_substr($priceStr, -2) . " $currencySymbol";
    }


    /**
     * Find a plan by its id or execute a callback.
     *
     * @param string $id
     * @param callable $callback
     *
     * @return Plan
     */
    public static function findOr(string $id, callable $callback)
    {
        $plans = self::all();

        /** @var Plan $plan */
        foreach ($plans as $plan) {
            if ($plan->id === $id) {
                return $plan;
            }
        }

        return $callback($id);
    }


    /**
     * Get all plans.
     *
     * @return Plan[]|Collection
     */
    public static function all()
    {
        return Cache::remember("plans", 30, function () {
            $plans = \Braintree\Plan::all();
            $collection = new Collection();

            foreach ($plans as $plan) {
                $pricePerMonth = round($plan->price / $plan->billingFrequency, 2);

                if (self::$highestPricePerMonth === null || $pricePerMonth > self::$highestPricePerMonth) {
                    self::$highestPricePerMonth = $pricePerMonth;
                }
            }

            foreach ($plans as $plan) {
                $collection->push(new self($plan));
            }

            // Don't forget to sort the values
            return $collection->sortByDesc("pricePerMonth")
                ->values();
        });
    }


    /**
     * Get the equivalent of a the old Striple plan using Braintree
     *
     * @param string $stripePlanId
     *
     * @return bool|string
     */
    public static function getStripeEquivalentPlanId(string $stripePlanId)
    {
        if (Str::contains($stripePlanId, "monthly")) {
            return "v4_monthly";
        } else if (Str::contains($stripePlanId, "annually")) {
            return "v4_annually";
        } else if (Str::contains($stripePlanId, "biannually")) {
            return "v4_biannually";
        } else {
            return false;
        }
    }
}

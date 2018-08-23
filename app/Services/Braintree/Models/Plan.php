<?php
namespace App\Services\Braintree\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class Plan
 * Implements the Braintree Plan object and add a few more details.
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 * @mixin \Braintree\Plan
 *
 * @property-read int $billingDayOfMonth
 * @property-read int $billingFrequency
 * @property-read string $currencyIsoCode
 * @property-read string $description
 * @property-read string $id
 * @property-read string $name
 * @property-read float $price
 * @property-read string $trialDuration
 * @property-read string $trialDurationUnit
 * @property-read bool $trialPeriod
 * @property-read Carbon createdAt
 * @property-read Carbon updatedAt
 * ----
 * @property-read string $pricingDetails
 * @property-read int $pricePerMonth
 * @property-read int $discount
 * @property-read bool $freeHolidays
 * @property-read float|null $trialPrice
 * @property-read Carbon|null $trialStartDate
 * @property-read Carbon|null $trialEndDate
 * ----
 * @property-read AddOn[] $addOns
 * @property-read \Braintree\Discount[] $discounts
 */
class Plan extends BraintreeModel
{
    public function __construct(\Braintree\Plan $plan, float $highestPricePerMonth = null)
    {
        parent::__construct($plan);

        $this->price = floatval($this->price);
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

        if ($highestPricePerMonth !== null) {
            $this->discount = 100 - round($this->pricePerMonth * 100 / $highestPricePerMonth, 0);
        } else {
            $this->discount = null;
        }

        $addOns = [];
        foreach ($this->addOns as $addOn) {
            $addOns[] = new AddOn($addOn);
        }
        $this->addOns = $addOns;
    }


    /**
     * Get the plan trial end date
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
     * Get the trial price in euros
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
     * Convert a float to a well-formatted price
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
        return substr($priceStr, 0, -2) . "." . substr($priceStr, -2) . " $currencySymbol";
    }


    /**
     * Get all plans
     *
     * @return Collection
     */
    public static function all()
    {
        return Cache::remember("plans", 30, function () {
            $plans = \Braintree\Plan::all();
            $collection = new Collection();

            foreach ($plans as $plan) {
                $collection->push(new Plan($plan));
            }

            // Don't forget to sort the values
            return $collection->sortByDesc("pricePerMonth")->values();
        });
    }


    /**
     * Find a plan by its id or execute a callback
     *
     * @param string $id
     *
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
}

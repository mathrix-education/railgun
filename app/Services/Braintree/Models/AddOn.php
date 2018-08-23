<?php
namespace App\Services\Braintree\Models;

use Braintree\Base;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class AddOn
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Services\Braintree\Models
 * @since 4.1.0
 * @copyright Mathrix Education SA
 * @mixin \Braintree\AddOn
 *
 * @property-read float $amount
 * @property-read int|null $currentBillingCycle
 * @property-read string $description
 * @property-read string $id
 * @property-read string $kind
 * @property-read string $name
 * @property-read bool $neverExpires
 * @property-read int|null $numberOfBillingCycles
 * @property-read int|null $quantity
 * @property-read Carbon $createdAt
 * @property-read Carbon $updatedAt
 * ----
 * @property-read bool $hasTrial
 * @property-read int $billingFrequency
 * @property-read float $pricePerMonth
 * @property-read float $trialPrice
 */
class AddOn extends BraintreeModel
{
    public function __construct(\Braintree\AddOn $addOn)
    {
        parent::__construct($addOn);

        $this->amount = floatval($addOn->amount);
        $this->hasTrial = Str::contains($this->id, ["v4_biannually", "v4_annually"]);

        if (Str::contains($this->id, ["v4_biannually", "v4_annually"])) {
            $this->billingFrequency = 12;
        } else if (Str::contains($this->id, "v4_monthly")) {
            $this->billingFrequency = 1;
        }

        if (isset($this["billingFrequency"])) {
            $this->pricePerMonth = round($this->amount / $this->billingFrequency, 2);
        }

        $this->trialPrice = $this->getTrialPrice();
    }


    /**
     * Get the plan trial end date
     *
     * @return Carbon|null The plan trial en date, null if the plan has no trial
     */
    public function getTrialEndDate(): Carbon
    {
        if (!$this->hasTrial) {
            return null;
        }

        $nextThirtyFirstJuly = new Carbon("31st July");

        if ($nextThirtyFirstJuly->isPast()) {
            $nextThirtyFirstJuly->addYear();
        } else if ((new Carbon("1st May"))->isPast()) {
            $nextThirtyFirstJuly->addYear();
        }

        if (Str::contains($this->id, "v4_biannually")) {
            $nextThirtyFirstJuly->addYear();
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
        if (!$this->hasTrial) {
            return null;
        }

        $now = Carbon::now();
        $trialEndDate = $this->getTrialEndDate();

        $diffInMonths = $trialEndDate->diffInMonths($now);
        $diffInDays = $trialEndDate->diffInDays($now->addMonths($diffInMonths));

        return round($diffInMonths * $this->pricePerMonth + $diffInDays * ($this->pricePerMonth / 30), 2);
    }


    /**
     * @return Collection
     */
    public static function all()
    {
        return Cache::remember("addons", 30, function () {
            $addOns = \Braintree\AddOn::all();
            $collection = new Collection();

            foreach ($addOns as $addOn) {
                $collection->push(new AddOn($addOn));
            }

            // Don't forget to sort the values
            return $collection->sortByDesc("id")->values();
        });
    }


    /**
     * The findOr function, must be reimplemented
     *
     * @param string $id
     * @param callable $callback
     *
     * @return mixed
     */
    public static function findOr(string $id, callable $callback)
    {
        $addOns = self::all();

        /** @var AddOn $addOn */
        foreach ($addOns as $addOn) {
            if ($addOn->id === $id) {
                return $addOn;
            }
        }

        return $callback($id);
    }
}

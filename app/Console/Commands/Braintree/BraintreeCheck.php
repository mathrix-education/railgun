<?php
namespace App\Console\Commands\Braintree;

use App\Services\Braintree\Models\AddOn;
use App\Services\Braintree\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class BraintreeCheck.
 * Check that all Braintree plans and add-ons are present in order to run the application.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Braintree
 */
class BraintreeCheck extends Command
{
    private const PLANS = ["v4_monthly", "v4_annually", "v4_biannually"];
    protected $signature = "braintree:check";

    public function handle()
    {
        if (!$this->output->getFormatter()
            ->hasStyle('alert')) {
            $style = new OutputFormatterStyle('red');
            $this->output->getFormatter()
                ->setStyle('alert', $style);
        }

        Cache::clear();

        $this->line("Checking for plans");
        foreach (self::PLANS as $planId) {
            $plan = Plan::find($planId);

            if ($plan instanceof Plan) {
                $this->line("[<info>✓</info>] Plan `{$planId}` is present.");
            } else {
                $this->line("[<alert>✘</alert>] Missing plan `{$planId}`.");
            }
        }
        $this->line("");

        $this->line("Checking for add-ons");
        foreach (self::PLANS as $planId) {
            foreach (["30", "50"] as $percentage) {
                $addOnId = $planId . "_child_" . $percentage;
                $addOn = AddOn::find($addOnId);

                if ($addOn instanceof AddOn) {
                    $this->line("[<info>✓</info>] Add-on `{$addOnId}` is present.");
                } else {
                    $this->line("[<alert>✘</alert>] Missing add-on `{$addOnId}`.");
                }
            }
        }
        $this->line("");

        $this->line("Please check that the following information are correct.");
        $plans = Plan::all();
        $plansData = $plans->map(function (Plan $plan) {
            return [$plan->id, $plan->price . "€", $plan->pricePerMonth . "€"];
        });
        $this->table(["Plan id", "price", "pricePerMonth"], $plansData);

        $addOns = AddOn::all();
        $addOnsData = $addOns->map(function (AddOn $addOn) {
            return [$addOn->id, $addOn->amount . "€", $addOn->pricePerMonth . "€"];
        });
        $this->table(["Add-on id", "price", "pricePerMonth"], $addOnsData);
    }
}

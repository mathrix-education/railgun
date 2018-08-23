<?php
namespace App\Console\Commands\Braintree;

use App\Services\Braintree\Models\AddOn;
use App\Services\Braintree\Models\Plan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class CheckModels
 * Check that all Braintree plans and add-ons are present in order to run the application.
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Console\Commands\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class BraintreeCheck extends Command
{
    protected $signature = "braintree:check";
    private const PLANS = ["v4_monthly", "v4_annually", "v4_biannually"];


    public function handle()
    {
        if (!$this->output->getFormatter()->hasStyle('alert')) {
            $style = new OutputFormatterStyle('red');
            $this->output->getFormatter()->setStyle('alert', $style);
        }


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
        $planData = $plans->map(function (Plan $plan) {
            return [$plan->id, $plan->pricePerMonth . "€"];
        });
        $this->table(["id", "pricePerMonth"], $planData);
    }
}

<?php

namespace App\Console\Commands\Braintree;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\User;
use Braintree\Transaction;
use Braintree_TransactionSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class NewPayPalCheck.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Braintree
 */
class NewPayPalCheck extends Command
{
    protected $signature = "braintree:new-paypal-check";


    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function handle()
    {
        $customersIds = new Collection();

        $this->line("Searching for PayPal transactions...");
        $transactions = Transaction::search([
            Braintree_TransactionSearch::paymentInstrumentType()->is("paypal_account"),
            Braintree_TransactionSearch::status()->is("settled")
        ]);
        $this->line("Got {$transactions->maximumCount()} transactions.");

        $this->line("Parsing transactions...");
        $this->output->progressStart($transactions->maximumCount());
        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $customersIds->push($transaction->customerDetails->id);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $customersIds = $customersIds->unique();

        $this->line("{$customersIds->count()} customers to notify.");

        $this->line("Writing in user settings...");
        $this->output->progressStart($customersIds->count());
        foreach ($customersIds as $customersId) {
            $user = User::where("braintree_id", "=", $customersId)->firstOrFail();
            $settings = $user->settings;
            $settings["paypal_auto"] = true;
            $user->settings = $settings;
            $user->save();

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->line("Done.");
    }
}

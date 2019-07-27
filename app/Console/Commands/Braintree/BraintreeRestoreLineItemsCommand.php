<?php

namespace App\Console\Commands\Braintree;

use App\Console\Commands\BoxStyle;
use App\Observers\TransactionObserver;
use App\Services\Braintree\Models\Transaction;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class BraintreeRestoreLineItemsCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "braintree:restore:line-items";


    public function handle()
    {
        $transactions = Transaction::all();
        $observer = new TransactionObserver();

        $this->line("Restoring line items");

        $this->progressStart($transactions->count());
        foreach ($transactions as $transaction) {
            $observer->saved($transaction);
            $this->progressAdvance();
        }
        $this->progressFinish();
    }
}

<?php


namespace App\Console\Commands\Braintree;


use App\Console\Commands\BoxStyle;
use App\Services\Braintree\Models\Subscription;
use App\Services\Braintree\Models\Transaction;
use Braintree\ResourceCollection;
use Braintree\Transaction as BraintreeTransaction;
use Braintree\TransactionSearch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class BraintreeSyncTransactionsCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "braintree:sync:transactions";


    public function handle()
    {
        $iterator = Carbon::createFromDate(2018, 9, 1);

        while ($iterator->isPast()) {
            $collection = gateway()->transaction()->search([
                // Limited to 10k customers per year. Thanks Julien for my 5k€!
                TransactionSearch::createdAt()->between($iterator, $iterator->clone()->addMonth())
            ]);

            $this->line("Syncing transactions created in {$iterator->monthName} {$iterator->year}");
            $this->processCollection($collection);

            $iterator = $iterator->addMonth();
        }
    }


    private function processCollection(ResourceCollection $collection)
    {
        if ($collection->maximumCount() === 0) {
            return;
        }

        $this->progressStart($collection->maximumCount());

        $transactions = new Collection();

        /**
         * @var BraintreeTransaction $braintreeTransaction
         */
        foreach ($collection as $braintreeTransaction) {
            $transactions->push($braintreeTransaction);
        }

        $transactions = $transactions->sortBy("createdAt");

        foreach ($transactions as $braintreeTransaction) {
            /** @var Transaction $transaction */
            $transaction = Transaction::query()
                ->where("id", "=", $braintreeTransaction->id)
                ->first();

            if ($transaction === null) {
                Transaction::createFromBraintreeTransaction($braintreeTransaction);
            } else {
                $transaction->fill(Transaction::toEloquent($braintreeTransaction));
                $transaction->save();
            }

            $this->progressAdvance();
        }

        $this->progressFinish();
    }
}

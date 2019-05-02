<?php

namespace App\Console\Commands;

use App\Mails\TransactionSuccessMail;
use App\Models\Transaction;
use App\Models\User;
use Braintree\Transaction as BraintreeTransaction;
use Braintree\TransactionLineItem;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class ResendTransactionSuccessMailCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.3
 */
class ResendTransactionSuccessMailCommand extends BaseCommand
{
    protected $signature = "mail:transaction";


    public function handle()
    {
        $transactionId = $this->ask("Enter the Braintree Transaction id :");

        /** @var Transaction $transaction */
        $transaction = Transaction::query()->where("id", "=", $transactionId)->firstOrFail();
        $this->line("Found transaction `{$transaction->id}`");

        /** @var User $user */
        $user = $transaction->customer;
        $this->line("Found user {$user->email}");

        /** @var BraintreeTransaction $braintreeTransaction */
        $braintreeTransaction = BraintreeTransaction::find($transactionId);
        $this->line("Found Braintree Transaction `{$braintreeTransaction->id}`");

        /** @var TransactionLineItem[] $lineItems */
        $lineItems = TransactionLineItem::findAll($transactionId);

        $this->line("Sending email...");
        Mail::to($user->email)->sendNow(
            new TransactionSuccessMail(
                $user,
                $braintreeTransaction,
                $lineItems
            ));

        $this->line("Done.");
    }
}

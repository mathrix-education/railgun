<?php


namespace App\Observers;


use App\Services\Braintree\Models\LineItem;
use App\Services\Braintree\Models\Transaction;
use Braintree\TransactionLineItem;

class TransactionObserver
{
    public function saved(Transaction $transaction)
    {
        /** @var TransactionLineItem[] $lineItems */
        $lineItems = gateway()->transactionLineItem()->findAll($transaction->id);

        foreach ($lineItems as $lineItem) {
            LineItem::createFromBraintreeLineItem($lineItem, $transaction->id);
        }
    }
}

<?php

namespace App\Events;

use Braintree\Transaction;

/**
 * Class TransactionSuccessEvent.
 * Fired when a transaction is successfully paid.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class TransactionSuccessEvent extends BaseEvent
{
    public $transaction;


    /**
     * TransactionSuccessEvent constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}

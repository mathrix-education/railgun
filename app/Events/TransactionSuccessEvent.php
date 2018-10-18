<?php

namespace App\Events;

use Braintree\Transaction;

/**
 * Class TransactionSuccessEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
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

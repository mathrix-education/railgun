<?php

namespace App\Events;

use Braintree\Transaction;

/**
 * Class TransactionRefundSuccessEvent.
 * Fired when a transaction refund is successful.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class TransactionRefundSuccessEvent extends BaseEvent
{
    /** @var Transaction $transaction */
    public $transaction;


    /**
     * TransactionRefundSuccessEvent constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}

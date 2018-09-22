<?php

namespace App\Events;

use Braintree\Transaction;

/**
 * Class TransactionRefundSuccessEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
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

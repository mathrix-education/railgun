<?php
namespace App\Events;

use Braintree\Transaction;

/**
 * Class TransactionSuccessEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Events
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

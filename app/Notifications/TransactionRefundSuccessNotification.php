<?php
namespace App\Notifications;

use App\Mails\TransactionRefundMail;
use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;

/**
 * Class TransactionRefundSuccessNotification.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Notifications
 */
class TransactionRefundSuccessNotification extends MailNotification
{
    /** @var User $user */
    private $user;
    /** @var Transaction $transaction */
    private $transaction;
    /** @var LineItem[] $lineItems */
    private $lineItems;
    /** @var Transaction $refund */
    private $refund;

    /**
     * TransactionRefundSuccessNotification constructor.
     *
     * @param User        $user
     * @param Transaction $transaction
     */
    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->refund = $transaction;
        $this->transaction = Transaction::find($transaction->refundedTransactionId);
        $this->lineItems = LineItem::findAll($transaction->id);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return TransactionRefundMail
     */
    public function toMail()
    {
        return (new TransactionRefundMail($this->user, $this->transaction, $this->lineItems, $this->refund))
            ->to($this->user->email);
    }
}

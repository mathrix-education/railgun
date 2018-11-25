<?php

namespace App\Notifications;

use App\Mails\TransactionSuccessMail;
use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use GuzzleHttp\Client;

/**
 * Class TransactionSuccessNotification.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class TransactionSuccessNotification extends MailNotification
{
    /** @var User $user */
    private $user;
    /** @var Transaction $transaction */
    private $transaction;
    /** @var LineItem[] $lineItems */
    private $lineItems;

    /**
     * TransactionSuccessNotification constructor.
     *
     * @param User        $user
     * @param Transaction $transaction
     */
    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->lineItems = LineItem::findAll($transaction->id);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return TransactionSuccessMail
     * @throws \Exception
     */
    public function toMail()
    {
        return (new TransactionSuccessMail($this->user, $this->transaction, $this->lineItems))
            ->to($this->user->email);
    }
}

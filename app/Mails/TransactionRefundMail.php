<?php

namespace App\Mails;

use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use Braintree\TransactionSearch;
use Illuminate\Support\Facades\Cache;

/**
 * Class TransactionRefundMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class TransactionRefundMail extends BaseMail
{
    /** @var User $user */
    private $user;
    /** @var Transaction $transaction */
    private $transaction;
    /** @var LineItem[] $lineItems */
    private $lineItems;
    /** @var Transaction $transaction */
    private $refund;


    public function __construct(User $user, Transaction $transaction, array $lineItems, Transaction $refund)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->lineItems = $lineItems;
        $this->refund = $refund;

        parent::__construct();
    }


    /**
     * Mock the Mail.
     *
     * @return TransactionRefundMail
     */
    public static function mock()
    {
        $user = User::random();
        $refund = Cache::remember("mail-transaction-refund_refund", 5, function () {
            return Transaction::search([
                TransactionSearch::type()->is("credit")
            ])
                ->firstItem();
        });
        $transaction = Cache::remember("mail-transaction-refund_refund", 5, function () use ($refund) {
            return Transaction::find($refund->refundedTransactionId);
        });

        return new self($user, $transaction, [], $refund);
    }


    /**
     * Build the mail.
     *
     * @return TransactionRefundMail
     */
    public function build()
    {
        return $this->subject("Votre remboursement de Mathrix")
            ->from("support@mathrix.fr")
            ->view("mails.transactions.refund", [
                "title" => "Reçu de Mathrix",
                "user" => $this->user,
                "transaction" => $this->transaction,
                "card" => $this->transaction->creditCardDetails,
                "lineItems" => $this->lineItems,
                "refund" => $this->refund,
            ]);
    }
}

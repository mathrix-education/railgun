<?php

declare(strict_types=1);

namespace App\Mails;

use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use Braintree\TransactionSearch;
use Illuminate\Support\Facades\Cache;
use function gateway;

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
        $this->user        = $user;
        $this->transaction = $transaction;
        $this->lineItems   = $lineItems;
        $this->refund      = $refund;

        parent::__construct();
    }

    /**
     * Mock the Mail.
     */
    public static function mock() : TransactionRefundMail
    {
        $user        = User::random();
        $refund      = Cache::remember('mail-transaction-refund_refund', 5, static function () {
            return gateway()->transaction()->search([TransactionSearch::type()->is('credit')])
                ->firstItem();
        });
        $transaction = Cache::remember('mail-transaction-refund_refund', 5, static function () use ($refund) {
            return gateway()->transaction()->find($refund->refundedTransactionId);
        });

        return new self($user, $transaction, [], $refund);
    }

    /**
     * Build the mail.
     */
    public function build() : TransactionRefundMail
    {
        return $this->subject('Votre remboursement de Mathrix')
            ->from('support@mathrix.fr')
            ->view('mails.transactions.refund', [
                'title' => 'Reçu de Mathrix',
                'user' => $this->user,
                'transaction' => $this->transaction,
                'card' => $this->transaction->creditCardDetails,
                'lineItems' => $this->lineItems,
                'refund' => $this->refund,
            ]);
    }
}

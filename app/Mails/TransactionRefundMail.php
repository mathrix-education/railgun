<?php
namespace App\Mails;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Class TransactionRefundMail.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Mails
 */
class TransactionRefundMail extends BaseMail
{
    /** @var User $user */
    private $user;
    /** @var \Braintree\Transaction $transaction */
    private $transaction;
    /** @var \Braintree\Transaction\LineItem[] $lineItems */
    private $lineItems;
    /** @var \Braintree\Transaction $transaction */
    private $refund;

    public function __construct(User $user, \Braintree\Transaction $transaction, array $lineItems,
                                \Braintree\Transaction $refund)
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
            return \Braintree\Transaction::search([
                \Braintree\TransactionSearch::type()
                    ->is("credit"),
            ])
                ->firstItem();
        });
        $transaction = Cache::remember("mail-transaction-refund_refund", 5, function () use ($refund) {
            return \Braintree\Transaction::find($refund->refundedTransactionId);
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
        $this->subject("Votre remboursement de Mathrix");

        return $this->view("emails.transactions.refund", [
            "title" => "Reçu de Mathrix",
            "user" => $this->user,
            "transaction" => $this->transaction,
            "lineItems" => $this->lineItems,
            "refund" => $this->refund,
        ]);
    }
}

<?php
namespace App\Mails;

use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use Cache;

/**
 * Class TransactionSuccessMail.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Mails
 */
class TransactionSuccessMail extends BaseMail
{
    /** @var Transaction $transaction */
    protected $transaction;
    /** @var LineItem[] $lineItems */
    protected $lineItems;
    /** @var User $user */
    private $user;

    public function __construct(User $user, Transaction $transaction, array $lineItems)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->lineItems = $lineItems;

        parent::__construct();
    }

    /**
     * Mock the Mail.
     *
     * @param string|null $transactionId
     *
     * @return TransactionSuccessMail
     */
    public static function mock(string $transactionId = null): self
    {
        $user = User::inRandomOrder()
            ->first();

        if ($transactionId === null) {
            $transaction = Cache::remember("TransactionSuccessMail_transaction", 1, function () {
                return Transaction::search([])
                    ->firstItem();
            });
        } else {
            $transaction = Cache::remember("TransactionSuccessMail_transaction_$transactionId", 1, function () use ($transactionId) {
                return Transaction::find($transactionId);
            });
        }

        /** @var LineItem[] $transactionLineItems */
        $transactionLineItems = LineItem::findAll($transaction->id);

        return new self($user, $transaction, $transactionLineItems);
    }

    /**
     * Build the mail.
     *
     * @return TransactionSuccessMail
     */
    public function build()
    {
        $this->subject("Votre reçu de Mathrix");

        return $this->view("emails.transactions.success", [
            "title" => "Reçu de Mathrix",
            "user" => $this->user,
            "transaction" => $this->transaction,
            "lineItems" => $this->lineItems,
        ]);
    }
}

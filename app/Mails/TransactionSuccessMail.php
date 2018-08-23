<?php
namespace App\Mails;

use App\Models\User;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use Cache;

/**
 * Class TransactionSuccessMail
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Mails
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class TransactionSuccessMail extends BaseMail
{
    /** @var User $user */
    private $user;
    /** @var Transaction $transaction */
    protected $transaction;
    /** @var LineItem[] $lineItems */
    protected $lineItems;


    public function __construct(User $user, Transaction $transaction, array $lineItems)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->lineItems = $lineItems;

        parent::__construct();
    }


    /**
     * Mock the Mail
     *
     * @param string|null $transactionId
     *
     * @return TransactionSuccessMail
     */
    public static function mock(string $transactionId = null): TransactionSuccessMail
    {
        $user = User::inRandomOrder()->first();

        if ($transactionId === null) {
            $transaction = Cache::remember("TransactionSuccessMail_transaction", 1, function () {
                return Transaction::search([])->firstItem();
            });
        } else {
            $transaction = Cache::remember("TransactionSuccessMail_transaction_$transactionId", 1, function () use ($transactionId) {
                return Transaction::find($transactionId);
            });
        }

        /** @var LineItem[] $transactionLineItems */
        $transactionLineItems = LineItem::findAll($transaction->id);

        return new TransactionSuccessMail($user, $transaction, $transactionLineItems);
    }


    /**
     * Build the mail
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
            "lineItems" => $this->lineItems
        ]);
    }
}

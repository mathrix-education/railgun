<?php

namespace App\Mails;

use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Braintree\Transaction;
use Braintree\Transaction\LineItem;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Class TransactionSuccessMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
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
        $user = User::random();

        if ($transactionId === null) {
            // No specific transaction was given: get the first one
            /** @var Transaction $transaction */
            $transaction = Cache::remember("TransactionSuccessMail_transaction", 1, function () {
                return Transaction::search([])->firstItem();
            });
        } else {
            /** @var Transaction $transaction */
            $transaction = Cache::remember("TransactionSuccessMail_transaction_$transactionId", 1, function () use ($transactionId) {
                return Transaction::find($transactionId);
            });
        }

        /** @var LineItem[] $transactionLineItems */
        $transactionLineItems = LineItem::findAll($transaction->id);

        if (!empty($transaction->planId)) {
            /** @var Plan $plans */
            $plan = Plan::find($transaction->planId);

            $subscriptionLine = new stdClass();
            $subscriptionLine->kind = "debit";
            $subscriptionLine->name = $plan->name;
            $subscriptionLine->quantity = 1;
            $subscriptionLine->unitAmount = $transaction->amount;
            $subscriptionLine->totalAmount = $transaction->amount;

            $transactionLineItems[] = $subscriptionLine;
        }

        return new self($user, $transaction, $transactionLineItems);
    }


    /**
     * Build the mail.
     *
     * @return TransactionSuccessMail
     */
    public function build()
    {
        return $this->subject("Votre reçu de Mathrix")
            ->from("support@mathrix.fr")
            ->view("mails.transactions.success", [
                "title" => "Reçu de Mathrix",
                "user" => $this->user,
                "transaction" => $this->transaction,
                "lineItems" => $this->lineItems,
                "card" => $this->transaction->creditCardDetails ?? null,
                "paypal" => $this->transaction->paypalDetails ?? null
            ]);
    }
}

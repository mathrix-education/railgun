<?php

namespace App\Services\Braintree\Models;

use App\Models\User;
use Braintree\Exception\NotFound as BraintreeNotFoundException;
use Braintree\Transaction as BraintreeTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Exceptions\Models\Models\ModelNotFoundException;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class Transaction.
 * Transactions are immutable by definition.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 *
 * @property string $id
 * @property string $type
 * @property float $amount
 * @property float $refunded_amount
 * @property string $currency
 * @property string $status
 * @property string $payment_instrument_type
 * @property array $processor_response
 * @property string $gateway_rejection_reason
 * @property string|null $refunded_transaction_id
 * @property string $customer_id
 * @property string $subscription_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property-read LineItem[]|Collection $lineItems The transaction line items.
 * @property-read Transaction $parent The parent transaction. If not null, it means that the current transaction is a
 *   refund.
 * @property-read Transaction[]|Collection $refunds The child refund transactions. If is not an empty collection, it
 *   means that the current transaction is a sale.
 * @property-read User $user The user associated with the transaction.
 * @property-read Subscription $subscription The subscription associated with this transaction, if any.
 * ---
 * @property-read BraintreeTransaction $transaction
 * @property-read bool $partially_refunded
 * @property-read bool $totally_refunded
 */
class Transaction extends BaseModel
{
    public $incrementing = false;
    protected $fillable = [
        "id",
        "type",
        "amount",
        "refunded_amount",
        "currency",
        "status",
        "payment_instrument_type",
        "processor_response",
        "gateway_rejection_reason",
        "refund_transaction_id",
        "customer_id",
        "subscription_id",
        "created_at",
        "updated_at"
    ];
    protected $casts = [
        "processor_response" => "array"
    ];

    /** @var BraintreeTransaction $_transaction */
    private $_transaction;


    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, "refund_transaction_id");
    }


    /**
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Transaction::class, "refund_transaction_id");
    }


    /**
     * @return HasMany
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class);
    }


    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "customer_id", "braintree_id");
    }


    /**
     * @return BelongsTo
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }


    /**
     * @return BraintreeTransaction
     * @throws ModelNotFoundException
     */
    public function getTransactionAttribute(): BraintreeTransaction
    {
        $this->_transaction = $this->asBraintreeTransaction();

        return $this->_transaction;
    }


    public function getPartiallyRefundedAttribute(): bool
    {
        return $this->refunds->sum("amount") > 0;
    }


    public function getTotallyRefundedAttribute(): bool
    {
        return $this->refunds->sum("amount") >= $this->amount;
    }


    /**
     * @return BraintreeTransaction
     * @throws ModelNotFoundException
     */
    public function asBraintreeTransaction(): BraintreeTransaction
    {
        try {
            return gateway()->transaction()->find($this->id);
        } catch (BraintreeNotFoundException $e) {
            throw new ModelNotFoundException(["id" => $this->id], null, $e);
        }
    }


    public static function toEloquent(BraintreeTransaction $transaction): array
    {
        $customerExists = User::query()
            ->where("braintree_id", "=", $transaction->customerDetails->id)
            ->exists();
        if ($customerExists) {
            $customerId = $transaction->customerDetails->id;
        } else {
            $customerId = null;
        }

        if ($transaction->subscriptionId !== null) {
            $subscriptionExists = Subscription::query()
                ->where("id", "=", $transaction->subscriptionId)
                ->exists();

            if ($subscriptionExists) {
                $subscriptionId = $transaction->subscriptionId;
            } else {
                $subscriptionId = null;
            }
        } else {
            $subscriptionId = null;
        }

        $data = [
            "id" => $transaction->id,
            "type" => $transaction->type,
            "amount" => floatval($transaction->amount),
            "discount_amount" => floatval($transaction->discountAmount),
            "refunded_amount" => 0,
            "currency" => $transaction->currencyIsoCode,
            "status" => $transaction->status,
            "payment_instrument_type" => $transaction->paymentInstrumentType,
            "processor_response" => [
                "type" => $transaction->processorResponseType,
                "code" => intval($transaction->processorResponseCode),
                "text" => $transaction->processorResponseText
            ],
            "gateway_rejection_reason" => $transaction->gatewayRejectionReason,
            "refund_transaction_id" => $transaction->refundedTransactionId,
            "customer_id" => $customerId,
            "subscription_id" => $subscriptionId,
            "created_at" => $transaction->createdAt,
            "updated_at" => $transaction->updatedAt
        ];

        return $data;
    }


    public static function createFromBraintreeTransaction(BraintreeTransaction $transaction)
    {
        /** @var Transaction $instance */
        $instance = (new self(self::toEloquent($transaction)));
        $instance->save();

        if ($transaction->type === BraintreeTransaction::SALE && $transaction->subscriptionId === null) {
            $lineItems = $transaction->lineItems ?? gateway()->transactionLineItem()->findAll($transaction->id);

            foreach ($lineItems as $braintreeLineItem) {
                LineItem::createFromBraintreeLineItem($braintreeLineItem, $transaction->id);
            }
        }

        return $instance;
    }


    /**
     * Pull the Transaction from the Braintree gateway.
     *
     * @throws ModelNotFoundException
     */
    public function sync(): void
    {
        $this->fill(self::toEloquent($this->asBraintreeTransaction()));
        $this->save();
    }
}

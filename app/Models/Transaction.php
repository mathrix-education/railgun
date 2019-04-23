<?php

namespace App\Models;

use Braintree\Transaction as BraintreeTransaction;
use Carbon\Carbon;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Transaction.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 *
 * @property string $id
 * @property string $amount
 * @property string $refund_amount
 * @property string $customer_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Transaction extends BaseModel
{
    public $incrementing = false;
    protected $fillable = [
        "id",
        "type",
        "plan_id",
        "amount",
        "refunded_amount",
        "refunded_transaction_id",
        "customer_id",
        "created_at",
        "updated_at"
    ];


    public static function createFromBraintreeTransaction(BraintreeTransaction $transaction)
    {
        $instance = new self([
            "id" => $transaction->id,
            "type" => $transaction->type,
            "plan_id" => $transaction->planId,
            "amount" => floatval($transaction->amount),
            "refunded_amount" => 0, // For now, we do not process it
            "refunded_transaction_id" => $transaction->refundedTransactionId,
            "customer_id" => $transaction->customerDetails->id,
            "created_at" => Carbon::instance($transaction->createdAt),
            "updated_at" => Carbon::instance($transaction->updatedAt)
        ]);

        $instance->save();

        return $instance;
    }


    public function asBraintreeTransaction()
    {
        return BraintreeTransaction::find($this->id);
    }


    public function customer()
    {
        return $this->belongsTo(User::class, "customer_id", "braintree_id");
    }


    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

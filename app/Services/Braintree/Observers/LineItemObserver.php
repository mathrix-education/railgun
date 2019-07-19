<?php


namespace App\Services\Braintree\Observers;


use App\Models\Product;
use App\Models\Purchase;
use App\Services\Braintree\Models\LineItem;
use Braintree\Transaction as BraintreeTransaction;

class LineItemObserver
{
    public function saving(LineItem $lineItem)
    {
        if (
            $lineItem->product_type === morph_alias(Product::class)
            && $lineItem->transaction->status === BraintreeTransaction::SETTLED
            && $lineItem->transaction->user !== null
        ) {
            $purchase = Purchase::query()->firstOrNew([
                "transaction_id" => $lineItem->transaction_id,
                "line_item_id" => $lineItem->id,
                "user_id" => $lineItem->transaction->user->id,
                "product_id" => $lineItem->product_code
            ]);

            $purchase->save();
        }
    }
}

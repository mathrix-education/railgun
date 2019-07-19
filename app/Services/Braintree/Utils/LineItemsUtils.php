<?php


namespace App\Services\Braintree\Utils;


use App\Models\Discount;
use App\Models\Product;
use Braintree\TransactionLineItem as BraintreeLineItem;

class LineItemsUtils
{
    /**
     * @param BraintreeLineItem $lineItem
     *
     * @return mixed|null
     */
    public static function getType(BraintreeLineItem $lineItem): ?string
    {
        if ($lineItem->productCode === null) {
            return null;
        } else if ($lineItem->kind === BraintreeLineItem::DEBIT) {
            return morph_alias(Product::class);
        } else {
            return morph_alias(Discount::class);
        }
    }
}

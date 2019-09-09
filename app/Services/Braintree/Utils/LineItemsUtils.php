<?php

declare(strict_types=1);

namespace App\Services\Braintree\Utils;

use App\Models\Discount;
use App\Models\Product;
use Braintree\TransactionLineItem as BraintreeLineItem;
use function morph_alias;

class LineItemsUtils
{
    /**
     * @return mixed|null
     */
    public static function getType(BraintreeLineItem $lineItem) : ?string
    {
        if ($lineItem->productCode === null) {
            return null;
        }

        if ($lineItem->kind === BraintreeLineItem::DEBIT) {
            return morph_alias(Product::class);
        }

        return morph_alias(Discount::class);
    }
}

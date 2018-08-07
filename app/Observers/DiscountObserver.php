<?php
namespace App\Observers;

use App\Models\Discount;
use App\Models\Product;

/**
 * Class DiscountObserver
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Observers
 * @since
 * @copyright Mathrix Education SA
 */
class DiscountObserver extends BaseObserver
{
    /**
     * @param Discount $discount
     *
     * @return bool
     */
    public function saving($discount)
    {
        // Sync notions
        if (isset($discount->link["products"])) {
            $toSync = [];
            foreach ($discount->link["products"] as $productId) {
                if (Product::whereId($productId)->exists()) {
                    /** @var Product $product */
                    $product = Product::find($productId);
                    if ($product->price !== null) {
                        $toSync[] = $productId;
                    }
                }
            }
            $discount->products()->sync($toSync);
        }

        if (isset($discount->link)) {
            unset($discount->link);
        }

        return true;
    }
}

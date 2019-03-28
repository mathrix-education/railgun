<?php

namespace App\Observers;

use App\Models\Discount;
use App\Models\Product;

/**
 * Class DiscountObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
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
                $exists = Product::query()->where("id", "=", $productId)->exists();

                if ($exists) {
                    /** @var Product $product */
                    $product = Product::query()->find($productId);

                    if ($product->price !== null) {
                        $toSync[] = $productId;
                    }
                }
            }
            $discount->products()
                ->sync($toSync);
        }

        if (isset($discount->link)) {
            unset($discount->link);
        }

        return true;
    }


    /**
     * @param Discount $discount
     *
     * @return bool
     */
    public function deleting($discount)
    {
        $discount->products()->detach();

        return true;
    }
}

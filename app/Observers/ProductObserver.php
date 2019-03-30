<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Class ProductObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class ProductObserver extends BaseObserver
{
    /**
     * @param Product $product
     *
     * @return bool
     */
    public function saving($product)
    {
        // Set the slug if needed
        if (empty($product->slug) ||
            ($product->isDirty("name") && !$product->isDirty("slug"))) {
            $product->slug = Str::slug($product->name);
        }

        // Sync children
        if (isset($product->link["children"])) {
            $toSync = [];
            foreach ($product->link["children"] as $order => $childId) {
                if (Product::query()->where("id", "=", $childId)->exists()) {
                    $toSync[$childId] = ["order" => $order];
                }
            }
            $product->children()
                ->sync($toSync);
        }

        if (isset($product->link)) {
            unset($product->link);
        }

        return true;
    }


    /**
     * @param Product $product
     *
     * @return bool
     */
    public function deleting($product)
    {
        $product->discounts()->detach();
        $product->parents()->detach();
        $product->children()->detach();

        return true;
    }
}

<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
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

        return true;
    }


    /**
     * @param Product $product
     *
     * @return bool
     * @throws ForeignConstraintsException
     */
    public function deleting($product)
    {
        $this->handleRelationDeletion(Product::class, "children");
        $product->discounts()->detach();

        return true;
    }
}

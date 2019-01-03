<?php
namespace App\Observers;

use App\Models\Product;

/**
 * Class ProductObserver.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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
        // Sluggify if needed
        if (empty($product->slug) ||
            ($product->isDirty("name") && !$product->isDirty("slug"))) {
            $product->slug = str_slug($product->name);
        }

        // Sync children
        if (isset($product->link["children"])) {
            $toSync = [];
            foreach ($product->link["children"] as $order => $childId) {
                if (Product::whereId($childId)
                    ->exists()) {
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
        $product->discounts()
            ->detach();
        $product->parents()
            ->detach();
        $product->children()
            ->detach();

        return true;
    }
}

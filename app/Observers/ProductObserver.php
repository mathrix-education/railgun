<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Product;

class ProductObserver extends BaseObserver
{
    /**
     * @throws ForeignConstraintsException
     */
    public function deleting(Product $product) : bool
    {
        $this->handleRelationDeletion($product, 'children');
        $product->items()->detach();
        $product->discounts()->detach();

        return true;
    }
}

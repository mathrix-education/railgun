<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Discount;
use App\Models\Product;

class DiscountObserver extends BaseObserver
{
    public function saving(Discount $discount): bool
    {
        // Sync notions
        if ($discount->exists && isset($discount->link['products'])) {
            $toSync = [];
            foreach ($discount->link['products'] as $productId) {
                /** @var Product $product */
                $product = Product::query()->find($productId);

                if (! $product || $product->price === null) {
                    continue;
                }

                $toSync[] = $product->id;
            }
            $discount->products()->sync($toSync);
        }

        if (isset($discount->link)) {
            unset($discount->link);
        }

        return true;
    }

    public function deleting(Discount $discount): bool
    {
        $discount->products()->detach();

        return true;
    }
}

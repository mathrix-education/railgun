<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\BaseTestCase;

/**
 * Class ProductTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class ProductTest extends BaseTestCase
{
    public function testDeliver()
    {
        $product = Product::random(["type", "=", "training"]);
        $user = User::random();

        $product->deliver($user);

        $sections = Product::query()
            ->where("parent_id", "=", $product->id)
            ->get();
        $lessons = Product::query()
            ->whereIn("parent_id", $sections->pluck("id"))
            ->get();
        $products = new Collection(array_merge([$product], $sections->all(), $lessons->all()));

        $products->each(function (Product $product) use ($user) {
            $this->assertTrue($user->hasPurchased($product));
        });
    }
}

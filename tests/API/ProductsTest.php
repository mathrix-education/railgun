<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class ProductsTests.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ProductsTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * GET /products/{productId}/recursive
     */
    public function testGetRecursive()
    {
        /** @var Product $product */
        $product = Product::query()->has("children")->inRandomOrder()->firstOrFail();

        $this->json("get", "/products/{$product->id}/recursive");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /products/slug/{productSlug}
     */
    public function testGetBySlug()
    {
        $this->assertRestGetBySuccess("slug");
    }


    /**
     * GET /products/{type}/{page}/{perPage}
     */
    public function testPaginateByType()
    {
        $this->json("get", "/products/all/0/100");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /products/buyable/{type=all}/{page}/{perPage}
     * Get all buyable products.
     */
    public function testBuyable()
    {
        $this->json("get", "/products/buyable/all/0/100");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /products/buyable/{type=training}/{page}/{perPage}
     * Get buyable trainings.
     */
    public function testBuyableTrainings()
    {
        $this->json("get", "/products/buyable/training/0/100");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * PATCH /products/{packId}/items
     */
    public function testItems()
    {
        $pack = Product::random(["type", "=", "pack"]);
        $trainingIds = Product::query()
            ->inRandomOrder()
            ->where("type", "=", "training")
            ->limit(5)
            ->get(["id"])
            ->pluck("id");

        $this->mockScope("admin");

        $this->json("patch", "/products/{$pack->id}/items", $trainingIds->toArray());

        $this->assertResponseOk();
        foreach ($trainingIds as $order => $trainingId) {
            $this->assertInDatabase("product_product", [
                "parent_id" => $pack->id,
                "child_id" => $trainingId,
                "order" => $order
            ]);
        }
    }


    /**
     *
     */
    public function testCart(): void
    {
        /** @var Discount $discount */
        $discount = Discount::query()
            ->whereHas("products", function (Builder $query) {
                $query->whereNotNull("price");
            })
            ->where("counter", ">", "0")
            ->whereNull("expire_at")
            ->inRandomOrder()
            ->firstOrFail();
        /** @var Collection|Product[] $products */
        $products = $discount->products()
            ->inRandomOrder()
            ->whereNotNull("price")
            ->limit($this->faker->numberBetween(1, 3))
            ->get();

        $sponsor = User::random();
        $sponsor->affiliate_factor = 2;
        $sponsor->save();

        $this->autoMockScope("post", "/products/cart");
        $this->json("post", "/products/cart", [
            "cart" => $products->pluck("id"),
            "discount_code" => $discount->code,
            "sponsor_email" => $sponsor->email
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}

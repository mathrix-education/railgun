<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

/**
 * Class ProductsTests.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ProductsTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest;


    /**
     * GET /products/{productId}/items/{page}/{perPage}
     */
    public function testItems(): void
    {
        $pack = Product::random(["type", "=", "pack"]);
        $trainingIds = Product::query()
            ->inRandomOrder()
            ->where("type", "=", "training")
            ->limit(5)
            ->get(["id"])
            ->pluck("id");

        $uri = "/products/{$pack->id}/items";
        JWT::autoScope("patch", $uri);
        $this->json("patch", $uri, $trainingIds->toArray());

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
     * GET /products/{productId}/recursive
     */
    public function testGetRecursive(): void
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
    public function testGetBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }


    /**
     * GET /products/{type}/{page}/{perPage}
     */
    public function testPaginateByType(): void
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
    public function testBuyable(): void
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
    public function testBuyableTrainings(): void
    {
        $this->json("get", "/products/buyable/training/0/100");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * POST /products/cart
     */
    public function testCart(): void
    {
        $user = User::random();
        JWT::actingAs($user);

        /** @var Discount $discount */
        $discount = Discount::query()
            ->whereHas("products", function (Builder $query) {
                $query->whereNotNull("price");
            })
            ->where("counter", ">", 0)
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
        $purchase = new Purchase([
            "transaction_id" => "fake-transaction-id",
            "user_id" => $sponsor->id,
            "product_id" => Product::random(["buyable", "=", true])->id
        ]);
        $purchase->save();

        $this->json("post", "/products/cart", [
            "cart" => $products->pluck("id"),
            "discount_code" => $discount->code,
            "sponsor_email" => $sponsor->email
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}

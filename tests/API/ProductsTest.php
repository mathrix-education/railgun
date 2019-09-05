<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
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
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:post",
        "std:get",
        "std:get:slug",
        "std:patch"
    ];


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

        $this->debug();
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
        // $this->assertOpenAPIResponse($this->response);
    }


    /**
     * GET /products/{type}
     */
    public function testPaginateByType(): void
    {
        $this->json("get", "/products/type/all");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }


    /**
     * GET /products/buyable/type/{type=all}
     * Get all buyable products.
     */
    public function testBuyable(): void
    {
        $this->json("get", "/products/buyable/type/all");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }


    /**
     * GET /products/buyable/type/{type=training}
     * Get buyable trainings.
     */
    public function testBuyableTrainings(): void
    {
        $this->json("get", "/products/buyable/type/training");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }


    /**
     * POST /products/cart
     */
    public function testCart(): void
    {
        $this->markTestSkipped("Waiting for new Stripe Implementation");
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
        $this->assertOpenAPIResponse($this->response);
    }
}

<?php

namespace Tests\API;

use App\Models\Product;
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
     * GET /products/buyable/{type}/{page}/{perPage}
     */
    public function testBuyable()
    {
        $this->json("get", "/products/buyable/all/0/100");

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
}

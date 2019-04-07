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
 * @since 5.0.0
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
     * GET /products/slug/{productSlug}
     */
    public function testBuyable()
    {
        $this->json("get", "/products/buyable/0/100");

        // Assertions
        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}

<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardDeleteTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APITestCase;

/**
 * Class DiscountsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DiscountsTest extends APITestCase
{
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardPatchTest, StandardDeleteTest;


    /**
     * GET /discounts/{discountId}/products/{page}/{perPage}
     */
    public function testProducts(): void
    {
        $this->assertRestRelationSuccess("products");
    }


    /**
     * PATCH /discounts/{discountId}/products
     */
    public function testPatchProducts(): void
    {
        /** @var Discount $discount */
        $discount = Discount::random();
        $productIds = Product::query()->inRandomOrder()->limit(5)->get()->pluck("id");

        $uri = "/discounts/{$discount->id}/products";
        JWT::autoScope("patch", $uri);
        $this->json("patch", $uri, $productIds->toArray());

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $productIds->each(function (int $productId) use ($discount) {
            $this->assertInDatabase("discount_product", [
                "discount_id" => $discount->id,
                "product_id" => $productId
            ]);
        });
    }


    /**
     * GET /discounts/code/{discountCode}
     */
    public function testByCode(): void
    {
        $this->assertRestGetBySuccess("code");
    }
}

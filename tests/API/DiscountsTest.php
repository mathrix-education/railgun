<?php

namespace Tests\API;

use App\Models\Discount;
use App\Models\Product;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardDeleteTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
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
    use StandardIndexTest, StandardPostTest, StandardGetTest, StandardDeleteTest;


    /**
     * PATCH /discounts/{discountId}
     */
    public function testPatch(): void
    {
        $productIds = Product::query()
            ->inRandomOrder()
            ->whereNotNull("price")
            ->limit(3)
            ->get(["id"])
            ->pluck("id")
            ->toArray();

        $this->assertRestPatchSuccess(
            function ($data) use ($productIds) {
                $data["link"]["products"] = $productIds;

                return $data;
            },
            function ($data) {
                unset($data["link"]);

                return $data;
            }
        );

        $discountId = $this->getJsonResponseValue("id");
        foreach ($productIds as $productId) {
            $this->assertInDatabase("discount_product", [
                "discount_id" => $discountId,
                "product_id" => $productId
            ]);
        }
    }


    /**
     * GET /discounts/{discountId}/products/{page}/{perPage}
     */
    public function testGetProducts(): void
    {
        /** @var Discount $discount */
        $discount = Discount::query()->has("products")->firstOrFail();

        $uri = "/discounts/{$discount->id}/products/0/100";
        JWT::autoScope("get", $uri);
        $this->json("get", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * PATCH /discounts/{discountId}/products
     */
    public function testPatchProducts(): void
    {
        /** @var Discount $discount */
        $discount = Discount::random();
        $productIds = Product::query()->inRandomOrder()->limit(5)->get()->pluck("id");

        $uri = "/discounts/{$discount->id}/products/0/100";
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

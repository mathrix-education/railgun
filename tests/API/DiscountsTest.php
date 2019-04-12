<?php

namespace Tests\API;

use App\Models\Product;
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
     * GET /discounts/code/{discountCode}
     */
    public function testGetByCode(): void
    {
        $this->assertRestGetBySuccess("code");
    }
}

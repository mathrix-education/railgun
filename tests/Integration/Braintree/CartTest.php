<?php

namespace Tests\Integration\Braintree;

use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class CartTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class CartTest extends BraintreeTestCase
{
    /**
     * POST /transactions/buy
     * Test buy with a discount code applicable on at least one product of the cart.
     *
     * @throws Http400BadRequestException
     */
    public function testBuy()
    {
        Notification::fake();

        /** @var Discount $discount */
        $discount = Discount::query()
            ->whereHas("products", function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->whereNotNull("price");
            })
            ->where("counter", ">", 0)
            ->firstOrFail();
        $counterBefore = $discount->counter;
        $productIds = $discount->products()
            ->whereNotNull("price")
            ->get()
            ->pluck("id");
        $user = $this->getBraintreeCustomer();
        $this->setUser($user); // IM HERE

        $this->json("post", "/products/buy", [
            "cart" => $productIds,
            "discount" => $discount->code,
        ]);

        /** @var Discount $discount */
        $discount->refresh();
        $this->assertResponseOk();
        $this->assertInDatabase("purchases", ["user_id" => $user->id, "product_id" => $productIds[0]]);
        $this->assertEquals($discount->counter, $counterBefore - 1);
    }


    /**
     * Test sale products to an user.
     *
     * POST /transactions/sale
     *
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function testSale()
    {
        Notification::fake();

        $customer = $this->getBraintreeCustomer();
        $support = User::where("type", "=", "support")
            ->firstOrFail();
        $this->setUser($support);

        $productIds = Product::query()
            ->inRandomOrder()
            ->whereNotNull("price")
            ->limit(random_int(1, 5))
            ->get()
            ->pluck("id")
            ->toArray();

        $this->json("post", "/products/sale", [
            "cart" => $productIds,
            "user_id" => $customer->id,
        ]);
        $this->assertResponseOk();
        $this->assertInDatabase("purchases", ["user_id" => $customer->id, "product_id" => $productIds[0]]);
    }
}

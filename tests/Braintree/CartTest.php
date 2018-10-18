<?php

namespace Tests\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Class CartIntegrationTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class CartTest extends BraintreeTestCase
{
    /**
     * Test buy with a discount code applicable on at least one product of the cart.
     *
     * POST /transactions/buy
     *
     * @throws Http400InvalidRequestException
     */
    public function testBuy()
    {
        Notification::fake();

        /** @var Discount $discount */
        $discount = Discount::whereHas("products", function (\Illuminate\Database\Eloquent\Builder $q) {
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
        $this->setUser($user);

        $this->json("post", "/products/buy", [
            "cart" => $productIds,
            "discount" => $discount->code,
        ]);

        /** @var Discount $discount */
        $discount = Discount::find($discount->id);
        $this->assertHttp200Success();
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

        $productIds = Product::inRandomOrder()
            ->whereNotNull("price")
            ->limit(random_int(1, 5))
            ->get()
            ->pluck("id")
            ->toArray();

        $this->json("post", "/products/sale", [
            "cart" => $productIds,
            "user_id" => $customer->id,
        ]);
        $this->assertHttp200Success();
        $this->assertInDatabase("purchases", ["user_id" => $customer->id, "product_id" => $productIds[0]]);
    }
}

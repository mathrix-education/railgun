<?php

namespace Tests\Integration\Braintree;

use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class ProductsBuyTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ProductsBuyTest extends BraintreeTestCase
{
    private function makeRequest(User $user, ?Collection $products, Discount $discount)
    {
        $this->setUser($user);

        if ($products === null) {
            $productIds = $discount->products()
                ->whereNotNull("price")
                ->limit(3)
                ->get()
                ->pluck("id");
        } else {
            $productIds = $products->pluck("id");
        }

        $payload = [
            "cart" => $productIds,
            "payment_method_nonce" => "fake-valid-no-billing-address-nonce"
        ];

        if ($discount != null) {
            $payload["discount"] = $discount->code;
        }

        $this->json("post", "/products/buy", $payload);

        return $productIds;
    }


    /**
     * POST /products/buy
     * Test buy with a discount code applicable on at least one product of the cart.
     *
     * @throws Http400BadRequestException
     */
    public function testBuy()
    {
        Notification::fake();

        /** @var Discount $discount */
        $discount = Discount::query()
            ->whereHas("products", function (Builder $q) {
                $q->whereNotNull("price");
            })
            ->where("counter", ">", 0)
            ->firstOrFail();
        $counterBefore = $discount->counter;

        $user = $this->getBraintreeCustomer();
        $productIds = $this->makeRequest($user, null, $discount);

        $discount->refresh();

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $productIds->each(function ($productId) use ($user) {
            $this->assertInDatabase("purchases", [
                "user_id" => $user->id,
                "product_id" => $productId
            ]);
        });
        $this->assertEquals($discount->counter, $counterBefore - 1);
        Notification::assertSentTo($user, TransactionSuccessNotification::class);
    }


    /**
     * Test with an expired discount.
     */
    public function testDiscountExpired()
    {
        // Prepare expired discount
        $expiredDiscount = Discount::random();
        $expiredDiscount->expire_at = Carbon::now()->subDay();
        $expiredDiscount->save();

        $this->makeRequest($this->getPristineCustomer(), null, $expiredDiscount);

        $this->assertResponseStatus(400);
        $this->assertOpenAPIResponse();
    }


    /**
     * Test with a non applicable discount.
     */
    public function testDiscountNotApplicable()
    {
        /** @var Discount $discount */
        $discount = Discount::query()
            ->where("counter", ">", "0")
            ->whereNull("expire_at")
            ->firstOrFail();
        /** @var Collection|Product[] $products */
        $products = Product::query()
            ->whereNotIn("id", $discount->products->pluck("id"))
            ->whereNotNull("price")
            ->limit(3)
            ->get();

        $this->makeRequest($this->getPristineCustomer(), $products, $discount);

        $this->assertResponseStatus(400);
        $this->assertOpenAPIResponse();
    }
}

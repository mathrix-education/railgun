<?php

namespace Tests\Integration\Braintree;

use App\Jobs\RefundSponsorJob;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Braintree\Test\Nonces;
use Braintree\Test\Transaction as TransactionTest;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

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
     * @return array
     */
    public function testBuy(): array
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

        $customer = $this->getPristineCustomer();
        $productIds = $this->makeRequest($customer, null, $discount);

        $discount->refresh();

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $productIds->each(function ($productId) use ($customer) {
            $this->assertInDatabase("purchases", [
                "user_id" => $customer->id,
                "product_id" => $productId
            ]);
        });
        $this->assertEquals($discount->counter, $counterBefore - 1);
        Notification::assertSentTo($customer, TransactionSuccessNotification::class);

        /** @var Purchase $purchase */
        $purchase = Purchase::query()
            ->where("user_id", "=", $customer->id)
            ->where("product_id", "=", $productIds->first())
            ->firstOrFail();

        return [$customer, $purchase->transaction_id];
    }


    /**
     * @depends testBuy
     * POST /products/buy
     * Test buy with a sponsor email.
     *
     * @param array $payload
     *
     * @return array
     * @throws Exception
     */
    public function testProductsBuyAffiliated(array $payload)
    {
        Queue::fake();
        Notification::fake();

        /**
         * @var User $sponsor
         * @var string $transactionId
         */
        [$sponsor, $transactionId] = $payload;
        TransactionTest::settle($transactionId); // Settle previous transaction immediately otherwise we cannot refund
        $sponsor->save();

        $customer = $this->getPristineCustomer();
        $customer->save();

        $products = Product::query()
            ->whereNotNull("price")
            ->inRandomOrder()
            ->limit($this->faker->numberBetween(1, 5))
            ->get();

        $this->setUser($customer);

        $this->json("post", "/products/buy", [
            "cart" => $products->pluck("id"),
            "payment_method_nonce" => Nonces::$transactableVisa,
            "sponsor_email" => $sponsor->email
        ]);


        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $products->each(function (Product $product) use ($customer) {
            $this->assertInDatabase("purchases", [
                "user_id" => $customer->id,
                "product_id" => $product->id
            ]);
        });

        $sponsor->refresh();
        $customer->refresh();
        Notification::assertSentTo($customer, TransactionSuccessNotification::class);
        Queue::assertPushed(RefundSponsorJob::class);

        return [$customer, $sponsor];
    }


    /**
     * @depends testProductsBuyAffiliated
     * Test the refund job.
     *
     * @param array $payload
     */
    public function testSponsorRefundJob(array $payload)
    {
        [$affiliate, $sponsor] = $payload;
        $refundJob = new RefundSponsorJob($affiliate, $sponsor);
        $refundJob->handle();
        $this->assertTrue($refundJob->refundResult->success);
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

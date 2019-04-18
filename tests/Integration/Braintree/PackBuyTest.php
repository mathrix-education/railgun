<?php

namespace Tests\Integration\Braintree;

use App\Models\Product;
use Braintree\Test\Nonces;
use Illuminate\Support\Facades\Mail;

/**
 * Class PackBuyTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.2-dev
 */
class PackBuyTest extends BraintreeTestCase
{
    /**
     *
     */
    public function testPackBuy()
    {
        Mail::fake();
        $customer = $this->getPristineCustomer();
        $this->setUser($customer);

        /** @var Product $pack */
        $pack = Product::query()
            ->where("type", "=", "pack")
            ->firstOrFail();

        $this->json("post", "/products/buy", [
            "cart" => [$pack->id],
            "payment_method_nonce" => Nonces::$transactableVisa
        ]);

        $customer->refresh();

        $this->assertResponseOk();
        $pack->items
            ->filter(function (Product $product) {
                return $product->type !== "premium";
            })
            ->map(function (Product $product) use ($customer) {
                return [
                    "user_id" => $customer->id,
                    "product_id" => $product->id
                ];
            })
            ->each(function (array $data) {
                $this->assertInDatabase("purchases", $data);
            });
        $this->assertNotNull($customer->premium_bypass_expires_at);
        $this->assertTrue($customer->premium);
    }
}

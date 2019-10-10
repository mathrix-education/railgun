<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use App\Models\Product;
use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;
use function factory;

class ProductsCheckoutTest extends APIStatelessTestCase
{
    public function invoiceCreateDataProvider()
    {
        return $this->cartesian([
            'tokens' => [
                ['tok_fr', 200],
                ['tok_threeDSecure2Required', 202],
            ],
        ]);
    }

    /**
     * POST /products/checkout
     *
     * @param string $token
     * @param int    $expectedStatus
     *
     * @dataProvider invoiceCreateDataProvider
     */
    public function testInvoiceCreate(string $token, int $expectedStatus)
    {
        $this->requester = factory(User::class)->create();
        JWT::actingAs($this->requester);

        $this->json(
            'post',
            '/products/checkout',
            [
                'card'       => ['token' => $token],
                'product_id' => Product::query()
                    ->where('buyable', '=', true)
                    ->firstOrFail()
                    ->id,
            ]
        );

        $this->assertResponseStatus($expectedStatus);
        $this->assertOpenAPIResponse($this->response);
    }
}

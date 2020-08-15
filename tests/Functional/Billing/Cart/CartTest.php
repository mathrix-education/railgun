<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Cart;

use Railgun\Billing\Cart\Cart;
use Tests\ApplicationTestCase;
use Tests\Utils\CartGenerator;

/**
 * @covers \Railgun\Billing\Cart\Cart
 */
class CartTest extends ApplicationTestCase
{
    public function testFullValid(): void
    {
        $cart = Cart::build(CartGenerator::input(['chapters', 'packs', 'trainings', 'coupon'], 'laser_month'));

        $this->assertGreaterThan(0, $cart->subTotal);
        $this->assertGreaterThan(0, $cart->total);
        $this->assertGreaterThan($cart->total, $cart->subTotal);

        $this->assertNotEmpty($cart->chapters);
        $this->assertNotEmpty($cart->packs);
        $this->assertNotEmpty($cart->trainings);
        $this->assertNotEmpty($cart->products);

        $this->assertEquals(
            $cart->products->count(),
            $cart->chapters->count() + $cart->packs->count() + $cart->trainings->count(),
        );
    }
}

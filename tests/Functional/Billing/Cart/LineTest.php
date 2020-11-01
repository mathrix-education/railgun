<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Cart;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Cart\Line;
use Railgun\Billing\Stripe\Repositories\PricesRepository;
use Railgun\Models\Coupon;
use Railgun\Models\Training;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Billing\Cart\Line
 */
class LineTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::fromProduct
     */
    public function testFromProduct(): void
    {
        $product = Training::random();
        $line    = Line::fromProduct($product);

        $this->assertEquals($product->price, $line->price);
    }

    /**
     * @covers ::fromPrice
     */
    public function testFromPrice(): void
    {
        $price = $this->app->make(PricesRepository::class)->get(PricesRepository::PREMIUM_MONTH);
        $line  = Line::fromPrice($price);

        $this->assertEquals($price->unit_amount, $line->price);
    }

    /**
     * @covers ::fromCoupon
     */
    public function testFromCoupon(): void
    {
        $coupon = factory(Coupon::class)->create([
            'type' => Coupon::TYPE_ABSOLUTE,
        ]);
        $line   = Line::fromCoupon($coupon, $coupon->value);

        $this->assertEquals(-$coupon->value, $line->price);
    }
}

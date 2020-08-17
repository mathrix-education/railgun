<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Cart;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Exceptions\AlreadyBoughtException;
use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Exceptions\DuplicateProductsException;
use Railgun\Billing\Exceptions\EmptyCartException;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProductsException;
use Railgun\Models\Acquisition;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Tests\ApplicationTestCase;
use Tests\Utils\CartInputGenerator;
use function array_merge;
use function factory;
use function resolveMorph;

/**
 * @covers \Railgun\Billing\Cart\Cart
 */
class CartTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    public function testFullValid(): void
    {
        $cart = Cart::build(CartInputGenerator::make(['chapters', 'packs', 'trainings', 'coupon'], 'laser_month'));
        $cart->validate();

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

        // products + plan + coupon => + 2
        $this->assertCount($cart->products->count() + 2, $cart->lines);
    }

    /**
     * @throws ApiErrorException
     */
    public function testNoCoupon(): void
    {
        /** @var Training $training */
        $training = Training::query()
            ->where('available', '=', true)
            ->firstOrFail();

        $cart = Cart::build([
            'trainings' => [$training->id],
        ]);

        $this->assertEquals(0, $cart->couponAmount);
        $this->assertEquals($training->price, $cart->total);
    }

    /**
     * @throws ApiErrorException
     */
    public function testCouponAmountPercent(): void
    {
        /** @var Coupon $coupon */
        $coupon = factory(Coupon::class)->create([
            'type'       => Coupon::TYPE_PERCENTAGE,
            'value'      => 30,
            'counter'    => -1,
            'expires_at' => null,
        ]);

        /** @var Training $training */
        $training = Training::query()
            ->where('available', '=', true)
            ->firstOrFail();

        $cart = Cart::build([
            'trainings' => [$training->id],
            'coupon'    => $coupon->id,
        ]);

        $this->assertEquals($training->price * 0.3, $cart->couponAmount);
        $this->assertEquals($training->price * 0.7, $cart->total);
    }

    /**
     * @throws ApiErrorException
     */
    public function testCouponAmountAbsolute(): void
    {
        $expectedCouponAmount = 500;

        /** @var Coupon $coupon */
        $coupon = factory(Coupon::class)->create([
            'type'       => Coupon::TYPE_ABSOLUTE,
            'value'      => $expectedCouponAmount,
            'counter'    => -1,
            'expires_at' => null,
        ]);

        /** @var Training $training */
        $training = Training::query()
            ->where('available', '=', true)
            ->firstOrFail();

        $cart = Cart::build([
            'trainings' => [$training->id],
            'coupon'    => $coupon->id,
        ]);

        $this->assertEquals($expectedCouponAmount, $cart->couponAmount);
        $this->assertEquals($training->price - $expectedCouponAmount, $cart->total);
    }

    /**
     * @throws ApiErrorException
     */
    public function testInvalidCoupon(): void
    {
        $this->expectException(InvalidCouponCodeException::class);

        Cart::build(array_merge(
            CartInputGenerator::make('chapters'),
            ['coupon' => 'invalid'],
        ));
    }

    /**
     * @covers \Railgun\Billing\Exceptions\EmptyCartException
     *
     * @throws ApiErrorException
     */
    public function testEmpty(): void
    {
        $cart = Cart::build([]);

        $this->expectException(EmptyCartException::class);
        $cart->validate();
    }

    /**
     * @covers \Railgun\Billing\Exceptions\DuplicateProductsException
     *
     * @throws ApiErrorException
     */
    public function testDuplicateProducts(): void
    {
        /** @var Pack $pack */
        $pack = Pack::query()->inRandomOrder()->has('trainings')->firstOrFail();
        $cart = Cart::build([
            'packs'     => [$pack->id],
            'trainings' => [$pack->trainings[0]->id],
        ]);

        $this->expectException(DuplicateProductsException::class);
        $cart->validate();
    }

    /**
     * @covers \Railgun\Billing\Exceptions\UnavailableProductsException
     *
     * @throws ApiErrorException
     */
    public function testUnavailableProducts(): void
    {
        $training = factory(Training::class)->create(['available' => false]);

        $cart = Cart::build(['trainings' => [$training->id]]);

        $this->expectException(UnavailableProductsException::class);
        $cart->validate();
    }

    /**
     * @covers \Railgun\Billing\Exceptions\AlreadyBoughtException
     *
     * @throws ApiErrorException
     */
    public function testOwnedProducts(): void
    {
        $cart = Cart::build(CartInputGenerator::make('trainings'));
        $user = factory(User::class)->create();

        Acquisition::query()->create([
            'user_id'        => $user->id,
            'product_type'   => resolveMorph($cart->products[0]),
            'product_id'     => $cart->products[0]->getKey(),
            'transaction_id' => $this->faker->regexify('in_[a-zA-Z0-9]{14}'),
        ]);

        $user->flushCache();

        $this->expectException(AlreadyBoughtException::class);
        $cart->validate($user);
    }

    /**
     * @covers \Railgun\Billing\Exceptions\AlreadyPremiumException
     *
     * @throws ApiErrorException
     */
    public function testAlreadyPremium(): void
    {
        $user = factory(User::class)->create(['premium_bypass' => true]);
        $cart = Cart::build(['plan' => 'laser_month']);

        $this->expectException(AlreadyPremiumException::class);
        $cart->validate($user);
    }

    /**
     * @throws ApiErrorException
     */
    public function testMetadataSerialization(): void
    {
        $cart1     = Cart::build(CartInputGenerator::make(['chapters', 'packs', 'trainings', 'coupon'], 'laser_month'));
        $metadata1 = $cart1->toMetadata();

        $cart2     = Cart::fromMetadata($metadata1);
        $metadata2 = $cart2->toMetadata();

        $this->assertEquals($metadata1, $metadata2);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Coupon;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\Coupon
 */
class CouponTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @return mixed[][] The test dataset (counter, expires_at, expectExhausted, expectExpired).
     */
    public function createValidityDataset(): array
    {
        return [
            [-1, null, false, false],
            [-1, Carbon::now()->addMonth(), false, false],
            [-1, Carbon::now()->subMonth(), false, true],
            [0, null, true, false],
            [0, Carbon::now()->addMonth(), true, false],
            [0, Carbon::now()->subMonth(), true, true],
        ];
    }

    /**
     * @dataProvider createValidityDataset
     * @covers ::getExhaustedAttribute
     * @covers ::getExpiredAttribute
     * @covers ::getValidAttribute
     *
     * @param int         $counter         The coupon counter.
     * @param Carbon|null $expires_at      The coupon expiration date.
     * @param bool        $expectExhausted If the coupon should be exhausted.
     * @param bool        $expectExpired   If the coupon should be expired.
     */
    public function testValidity(
        int $counter,
        ?Carbon $expires_at,
        bool $expectExhausted,
        bool $expectExpired
    ): void
    {
        /** @var Coupon $coupon */
        $coupon = factory(Coupon::class)->create([
            'counter'    => $counter,
            'expires_at' => $expires_at,
        ]);

        $this->assertEquals($expectExhausted, $coupon->exhausted);
        $this->assertEquals($expectExpired, $coupon->expired);
        $this->assertEquals(!$expectExhausted && !$expectExpired, $coupon->valid);
    }

    /**
     * @testWith [3, 2]
     *           [-1, -1]
     * @covers ::decrementCounter
     *
     * @param int $counter         The initial coupon counter.
     * @param int $expectedCounter The expected counter after decrement.
     */
    public function testDecrementCounter(int $counter, int $expectedCounter): void
    {
        $coupon = factory(Coupon::class)->create(['counter' => $counter]);
        $coupon->decrementCounter();

        $this->assertEquals($expectedCounter, $coupon->counter);
    }
}

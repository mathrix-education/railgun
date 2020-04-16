<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Cart;

use Illuminate\Database\Eloquent\Collection;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Exceptions\AlreadyBoughtException;
use Railgun\Billing\Exceptions\AlreadyPremiumException;
use Railgun\Billing\Exceptions\DuplicateProductsException;
use Railgun\Billing\Exceptions\InvalidCouponCodeException;
use Railgun\Billing\Exceptions\UnavailableProducts;
use Railgun\Models\Acquisition;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use Throwable;
use function array_key_exists;
use function array_map;
use function dd;
use function factory;
use function with;

class CartValidatorTest extends ApplicationTestCase
{
    /**
     * 1 pack and 5 trainings are created.
     * The first 3 trainings are included in the pack.
     *
     * @return mixed[][] The test dataset.
     */
    public function createValidateDataset(): array
    {
        try {
            $this->refreshApplication();

            /** @var Collection|Training[] $trainings */
            $trainings = factory(Training::class, 5)->create(['available' => true, 'order' => 0]);
            /** @var Pack $pack */
            $pack = factory(Pack::class)->create(['available' => true]);
            $pack->trainings()->attach(
                $trainings->pluck('id')
                    ->slice(0, 3)
                    ->mapWithKeys(static fn($id) => [$id => ['order' => 0]])
                    ->toArray(),
            );
            /** @var Coupon $couponAbsolute */
            $couponAbsolute = factory(Coupon::class)->create([
                'type'       => 'absolute',
                'value'      => 1000,
                'counter'    => -1,
                'expires_at' => null,
            ]);
            /** @var Coupon $couponPercent */
            $couponPercent = factory(Coupon::class)->create([
                'type'       => 'percentage',
                'value'      => 1000,
                'counter'    => -1,
                'expires_at' => null,
            ]);
        } catch (Throwable $e) {
            dd($e);
        }

        $dataset = [
            // #0: Only pack
            [
                'input' => [
                    'packIds' => [$pack->id],
                ],
            ],
            // #1: 3 trainings
            [
                'input' => [
                    'trainingIds' => $trainings->slice(0, 3)->pluck('id')->toArray(),
                ],
            ],
            // #2: 1 pack and 2 trainings, without intersection
            [
                'input' => [
                    'packIds'     => [$pack->id],
                    'trainingIds' => $trainings->slice(3, 2)->pluck('id')->toArray(),
                ],
            ],
            // #3: 1 training with premium
            [
                'input' => [
                    'trainingIds' => [$trainings[0]->id],
                    'premium'     => true,
                ],
            ],
            // #4: 1 training and a valid absolute coupon
            [
                'input' => [
                    'trainingIds' => [$trainings[0]->id],
                    'coupon'      => $couponAbsolute->id,
                ],
            ],
            // #5: 1 training and a valid percent coupon
            [
                'input' => [
                    'trainingIds' => [$trainings[0]->id],
                    'coupon'      => $couponPercent->id,
                ],
            ],
            // #6: 1 pack and 1 training which is part of the pack
            [
                'input'     => [
                    'packIds'     => [$pack->id],
                    'trainingIds' => [$trainings[0]->id],
                ],
                'exception' => DuplicateProductsException::class,
            ],
            // #7: 1 training and an invalid coupon code
            [
                'input'     => [
                    'trainingIds' => [$trainings[0]->id],
                    'coupon'      => 'invalid-coupon',
                ],
                'exception' => InvalidCouponCodeException::class,
            ],
            // #8: user already bought the training
            [
                'user'      => with(
                    factory(User::class)->create(),
                    static function (User $user) use ($trainings) {
                        $user->acquisitions()->save(new Acquisition([
                            'product_type'   => 'training',
                            'product_id'     => $trainings[0]->id,
                            'transaction_id' => 'in_mocked',
                        ]));

                        return $user;
                    },
                ),
                'input'     => [
                    'trainingIds' => [$trainings[0]->id],
                ],
                'exception' => AlreadyBoughtException::class,
            ],
            // #9: 1 training is not available
            [
                'input'     => [
                    'trainingIds' => [
                        factory(Training::class)->create(['available' => false, 'order' => 0])->id,
                    ],
                ],
                'exception' => UnavailableProducts::class,
            ],
            // #10: the user is already premium
            [
                'input'     => [
                    'trainingIds' => [$trainings[0]->id],
                    'premium'     => true,
                ],
                'user' => factory(User::class)->create(['premium_bypass' => true]),
                'exception' => AlreadyPremiumException::class,
            ],
        ];

        return array_map(static fn($line) => [$line], $dataset);
    }

    /**
     * @dataProvider createValidateDataset
     * @testdox      validates the cart
     *
     * @param mixed[] $dataset The test data.
     */
    public function testValidate(array $dataset): void
    {
        if (array_key_exists('exception', $dataset)) {
            $this->expectException($dataset['exception']);
        }

        $cart = new Cart($dataset['input']);

        if (array_key_exists('user', $dataset)) {
            $cart->user = $dataset['user'];
        }

        $result = $cart->validate();

        if (!array_key_exists('exception', $dataset)) {
            $this->assertNotNull($result);
        }
    }
}

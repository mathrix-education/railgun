<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Coupon;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use Throwable;
use function array_key_exists;
use function dd;
use function factory;

/**
 * @testdox Query validateCart
 */
class ValidateCartIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @param int[]       $packIds     The training ids.
     * @param int[]       $trainingIds The pack ids.
     * @param bool        $premium     If the premium product should be included.
     * @param string|null $coupon      The coupon code.
     */
    private function executeValidateCart(
        array $packIds,
        array $trainingIds,
        bool $premium,
        ?string $coupon
    ): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($packIds: [ID!], $trainingIds: [ID!], $premium: Boolean, $coupon: ID) {
                validateCart(
                    cart: {
                        packIds: $packIds
                        trainingIds: $trainingIds
                        premium: $premium
                        coupon: $coupon
                    }
                ) {
                    packs {
                        id
                        name
                        price
                        trainings {
                            id
                            name
                            price
                        }
                    }
                    trainings {
                        id
                        name
                        price
                    }
                    coupon {
                        id
                    }
                    subTotal
                    total
                }
            }
            GRAPHQL,
            [
                'packIds'     => $packIds,
                'trainingIds' => $trainingIds,
                'premium'     => $premium,
                'coupon'      => $coupon,
            ],
        );
    }

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

        return [
            'pack-only'                      => [
                [$pack->id],
                [],
            ],
            'trainings-only'                 => [
                [],
                $trainings->slice(0, 3)->pluck('id')->toArray(),
            ],
            'pack-trainings-no-intersection' => [
                [$pack->id],
                $trainings->slice(3, 2)->pluck('id')->toArray(),
            ],
            'premium'                        => [
                [],
                [$trainings[0]->id],
                true,
            ],
            'absolute-coupon'                => [
                [],
                [$trainings[0]->id],
                false,
                $couponAbsolute->id,
            ],
            'percentage-coupon'              => [
                [],
                [$trainings[0]->id],
                false,
                $couponPercent->id,
            ],
            'pack-trainings-intersection'    => [
                [$pack->id],
                $trainings->slice(0, 3)->pluck('id')->toArray(),
                false,
                null,
                ['code' => 'DUPLICATE_PRODUCTS'],
            ],
            'invalid-coupon'                 => [
                [],
                [$trainings[0]->id],
                false,
                'invalid-coupon',
                ['code' => 'INVALID_COUPON_CODE'],
            ],
        ];
    }

    /**
     * @dataProvider createValidateDataset
     * @testdox      returns the validated cart
     *
     * @param int[]         $packIds       The training ids.
     * @param int[]         $trainingIds   The pack ids.
     * @param bool          $premium       If the premium product should be included.
     * @param string|null   $coupon        The coupon code.
     * @param string[]|null $expectedError The expected error.
     */
    public function testValidate(
        array $packIds,
        array $trainingIds,
        bool $premium = false,
        ?string $coupon = null,
        ?array $expectedError = null
    ): void
    {
        $this->actingAsNewUser();

        $this->executeValidateCart($packIds, $trainingIds, $premium, $coupon);

        $this->assertResponseOk();

        if ($expectedError === null) {
            $this->assertNoErrors();
        } else {
            if (array_key_exists('message', $expectedError)) {
                $this->assertErrorMessage($expectedError['message']);
            }

            if (array_key_exists('code', $expectedError)) {
                $this->assertErrorCode($expectedError['code']);
            }
        }
    }
}

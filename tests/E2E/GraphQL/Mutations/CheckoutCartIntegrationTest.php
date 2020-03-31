<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_intersect;
use function trans;

/**
 * @testdox [E2E] Mutation checkoutCart
 */
class CheckoutCartIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    mutation($packIds: [ID!], $trainingIds: [ID!], $premium: Boolean, $coupon: ID, $paymentMethodId: String!) {
        checkoutCart(
            cart: {
                packIds: $packIds
                trainingIds: $trainingIds
                premium: $premium
                coupon: $coupon
            }
            paymentMethodId: $paymentMethodId
        ) {
            id
            amount_paid
            hosted_invoice_url
            invoice_pdf
            payment_intent {
                status
                client_secret
            }
            number
            status
        }
    }
    GRAPHQL;

    /**
     * Get the variables to build a valid cart.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    private function getVariables(string $paymentMethodId): array
    {
        /** @var Pack $pack */
        $pack    = Pack::query()
            ->inRandomOrder()
            ->where('available', '=', true)
            ->firstOrFail();
        $packIds = [$pack->id];

        /** @var Collection|Training[] $trainings */
        $trainingIds = Training::query()
            ->inRandomOrder()
            ->where('available', '=', true)
            ->whereDoesntHave('packs', static function (Builder $builder) use ($pack) {
                $builder->where('packs.id', '=', $pack->id);
            })
            ->limit(2)
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        return [
            'packIds'         => $packIds,
            'trainingIds'     => $trainingIds,
            'premium'         => true,
            'paymentMethodId' => $paymentMethodId,
        ];
    }

    /**
     * Assert that the given product ids were successfully delivered to a user.
     *
     * @param User  $user        The targeted user.
     * @param int[] $packIds     The pack ids to check.
     * @param int[] $trainingIds The training ids to check.
     * @param bool  $negate      Negate the assertion
     */
    private function assertProductsDelivered(User $user, array $packIds, array $trainingIds, bool $negate = false): void
    {
        // Check packs ownership
        $expectedPackIds = $packIds;
        $userPackIds     = $user->packs()->pluck('packs.id')->toArray();

        if (!$negate) {
            $this->assertEqualsCanonicalizing($expectedPackIds, $userPackIds);
        } else {
            $this->assertEmpty(array_intersect($expectedPackIds, $userPackIds));
        }

        // Check trainings ownership
        $expectedTrainingsIds = Training::query()
            ->whereHas('packs', fn(Builder $builder) => $builder->whereIn('packs.id', $packIds))
            ->pluck('trainings.id')
            ->unique()
            ->toArray();
        $expectedTrainingsIds = [...$expectedTrainingsIds, ...$trainingIds];
        $userTrainingIds      = $user->trainings()->pluck('trainings.id')->toArray();

        if (!$negate) {
            $this->assertEqualsCanonicalizing($expectedTrainingsIds, $userTrainingIds);
        } else {
            $this->assertEmpty(array_intersect($expectedTrainingsIds, $userTrainingIds));
        }
    }

    /**
     * Assert that the given product ids were not delivered to a user.
     *
     * @param User  $user        The targeted user.
     * @param int[] $packIds     The pack ids to check.
     * @param int[] $trainingIds The training ids to check.
     */
    private function assertProductsNotDelivered(User $user, array $packIds, array $trainingIds): void
    {
        $this->assertProductsDelivered($user, $packIds, $trainingIds, true);
    }

    /**
     * @return array The dataset for the ::testCheckoutSuccess method.
     */
    public function createCheckoutSuccessDataset(): array
    {
        return [
            'pm_card_fr'                    => [
                'pm_card_fr',
                Invoice::STATUS_PAID,
                PaymentIntent::STATUS_SUCCEEDED,
                true,
            ],
            'pm_card_threeDSecure2Required' => [
                'pm_card_threeDSecure2Required',
                Invoice::STATUS_OPEN,
                PaymentIntent::STATUS_REQUIRES_ACTION,
                false,
            ],
        ];
    }

    /**
     * @dataProvider createCheckoutSuccessDataset
     * @testdox      creates invoice using the token $paymentMethodId.
     *
     * @param string $paymentMethodId
     * @param string $expectedInvoiceStatus
     * @param string $expectedPaymentIntentStatus
     * @param bool   $expectProductDelivered
     */
    public function testCheckoutSuccess(
        string $paymentMethodId,
        string $expectedInvoiceStatus,
        string $expectedPaymentIntentStatus,
        bool $expectProductDelivered
    ): void {
        $user      = $this->actingAsNewUser();
        $variables = $this->getVariables($paymentMethodId);

        $this->graphQL(self::QUERY, $variables);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($expectedInvoiceStatus, $this->getData('checkoutCart.status'));
        $this->assertEquals($expectedPaymentIntentStatus, $this->getData('checkoutCart.payment_intent.status'));

        $user->refresh();
        $this->assertTrue($user->isStripeCustomer());

        if ($expectProductDelivered) {
            $this->assertProductsDelivered($user, $variables['packIds'], $variables['trainingIds']);
            $this->assertEquals($user->premium, $variables['premium']);
        } else {
            $this->assertProductsNotDelivered($user, $variables['packIds'], $variables['trainingIds']);
            $this->assertFalse($user->premium);
        }
    }

    /**
     * @return array The dataset for the ::testCheckoutFails method.
     */
    public function createCheckoutFailsDataset(): array
    {
        return [
            'pm_card_cvcCheckFail'                    => [
                'pm_card_cvcCheckFail',
                trans('stripe.errors.card.incorrect_cvc'),
            ],
            'pm_card_chargeDeclined'                  => [
                'pm_card_chargeDeclined',
                trans('stripe.errors.card.generic'),
            ],
            'pm_card_chargeDeclinedInsufficientFunds' => [
                'pm_card_chargeDeclinedInsufficientFunds',
                trans('stripe.errors.card.generic'),
            ],
            'pm_card_chargeDeclinedIncorrectCvc'      => [
                'pm_card_chargeDeclinedIncorrectCvc',
                trans('stripe.errors.card.incorrect_cvc'),
            ],
            'pm_card_chargeDeclinedExpiredCard'       => [
                'pm_card_chargeDeclinedExpiredCard',
                trans('stripe.errors.card.expired'),
            ],
            'pm_card_chargeDeclinedProcessingError'   => [
                'pm_card_chargeDeclinedProcessingError',
                trans('stripe.errors.card.generic'),
            ],
        ];
    }

    /**
     * @dataProvider createCheckoutFailsDataset
     * @testdox      fails to create in invoice using $paymentMethodId with error "$expectedError"
     *
     * @param string $paymentMethodId
     * @param string $expectedError
     */
    public function testCheckoutFails(string $paymentMethodId, string $expectedError): void
    {
        $user      = $this->actingAsNewUser();
        $variables = $this->getVariables($paymentMethodId);

        $this->graphQL(self::QUERY, $variables);

        $this->assertEquals($expectedError, $this->getContent('errors.0.message'));

        $user->refresh();
        $this->assertFalse($user->isStripeCustomer());
        $this->assertFalse($user->premium);
        $this->assertProductsNotDelivered($user, $variables['packIds'], $variables['trainingIds']);
    }
}

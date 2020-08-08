<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Stripe\Exception\ApiErrorException;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_intersect;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function trans;

/**
 * @testdox Mutation checkoutCart
 */
class CheckoutCartIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation(
        $chapters: [ID!]
        $packs: [ID!]
        $trainings: [ID!]
        $coupon: ID
        $paymentMethodId: String!
    ) {
        checkoutCart(
            cart: {
                chapters: $chapters
                packs: $packs
                trainings: $trainings
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
     * @param string $paymentMethodId The payment method id.
     *
     * @return mixed[]
     */
    private function getVariables(string $paymentMethodId): array
    {
        $chapterIds = Chapter::query()
            ->inRandomOrder()
            ->whereNotNull('stripe_price_id')
            ->limit(2)
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        /** @var Pack $pack */
        $pack    = Pack::query()
            ->inRandomOrder()
            ->where('available', '=', true)
            ->firstOrFail();
        $packIds = [$pack->id];

        $trainingIds = Training::query()
            ->inRandomOrder()
            ->where('available', '=', true)
            ->whereDoesntHave('packs', static function (Builder $builder) use ($pack): void {
                $builder->where('packs.id', '=', $pack->id);
            })
            ->limit(2)
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        return [
            'chapters'        => $chapterIds,
            'packs'           => $packIds,
            'trainings'       => $trainingIds,
            'paymentMethodId' => $paymentMethodId,
        ];
    }

    /**
     * Assert that the submitted products when delivered to the current user.
     *
     * @param mixed[] $cartInput The CartInput GraphQL Input.
     * @param bool    $negate    If the assertion should be negated.
     */
    private function assertProductsDelivered(array $cartInput, bool $negate = false): void
    {
        $expectedChapterIds  = $cartInput['chapters'] ?? [];
        $expectedPackIds     = $cartInput['packs'] ?? [];
        $expectedTrainingIds = $cartInput['trainings'] ?? [];

        if (array_key_exists('packs', $cartInput)) {
            $trainingsFromPacks = Training::query()
                ->whereHas('packs', static fn(Builder $builder) => $builder->whereIn('packs.id', $expectedPackIds))
                ->pluck('trainings.id')
                ->unique()
                ->toArray();

            $expectedTrainingIds = array_unique(array_merge($expectedTrainingIds, $trainingsFromPacks));
        }

        // Run assertions
        $this->assertUserOwnProducts('chapters', $expectedChapterIds, $negate);
        $this->assertUserOwnProducts('packs', $expectedPackIds, $negate);
        $this->assertUserOwnProducts('trainings', $expectedTrainingIds, $negate);
    }

    /**
     * Assert that the current user owns the given product ids.
     *
     * @param string $relation    The relation to test.
     * @param int[]  $expectedIds The product ids.
     * @param bool   $negate      If the assertion should be negated.
     */
    private function assertUserOwnProducts(
        string $relation,
        array $expectedIds,
        bool $negate = false
    ): void
    {
        /** @var Collection $models */
        $models = $this->getUser()->refresh()->{$relation};

        if (!$negate) {
            $this->assertEqualsCanonicalizing(
                array_intersect(
                    $models->pluck('id')->toArray(),
                    $expectedIds,
                ),
                $expectedIds,
                "User {$this->getUser()->id} does not own all the passed {$relation} ids",
            );
        } else {
            $this->assertEmpty(
                array_intersect(
                    $models->pluck('id')->toArray(),
                    $expectedIds,
                ),
                "User {$this->getUser()->id} owns all the passed {$relation} ids",
            );
        }
    }

    /**
     * @return mixed[][] The dataset for the ::testCheckoutSuccess method.
     */
    public function createCheckoutSuccessDataset(): array
    {
        return [
            'pm_card_fr'                    => [
                'pm_card_fr',
                'STATUS_PAID',
                'SUCCEEDED',
                true,
            ],
            'pm_card_threeDSecure2Required' => [
                'pm_card_threeDSecure2Required',
                'STATUS_OPEN',
                'REQUIRES_ACTION',
                false,
            ],
        ];
    }

    /**
     * @dataProvider createCheckoutSuccessDataset
     * @testdox      creates invoice using the token $paymentMethodId
     *
     * @param string $paymentMethodId             The payment method id to use.
     * @param string $expectedInvoiceStatus       The expected {@see Invoice} status.
     * @param string $expectedPaymentIntentStatus The expected {@see PaymentIntent} status.
     * @param bool   $expectProductDelivered      If the products should be delivered.
     */
    public function testCheckoutSuccess(
        string $paymentMethodId,
        string $expectedInvoiceStatus,
        string $expectedPaymentIntentStatus,
        bool $expectProductDelivered
    ): void
    {
        $user      = $this->actingAsGetUser();
        $variables = $this->getVariables($paymentMethodId);

        $this->graphQL(self::QUERY, $variables);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($expectedInvoiceStatus, $this->getData('checkoutCart.status'));
        $this->assertEquals($expectedPaymentIntentStatus, $this->getData('checkoutCart.payment_intent.status'));

        $user->refresh();
        $this->assertNotNull($user->stripe_id);

        if ($expectProductDelivered) {
            $this->assertProductsDelivered($variables);
        } else {
            $this->assertProductsDelivered($variables, true);
        }
    }

    /**
     * @return mixed[][] The dataset for the ::testCheckoutFails method.
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
                trans('stripe.errors.card.insufficient_funds'),
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
     * @param string $paymentMethodId The payment method id to use.
     * @param string $expectedError   The expected error message.
     */
    public function testCheckoutFails(string $paymentMethodId, string $expectedError): void
    {
        $user      = $this->actingAsGetUser();
        $variables = $this->getVariables($paymentMethodId);

        $this->graphQL(self::QUERY, $variables);

        $this->assertEquals($expectedError, $this->getContent('errors.0.message'));

        $user->refresh();
        $this->assertFalse($user->isStripeCustomer());
        $this->assertFalse($user->premium);
        $this->assertProductsDelivered($variables['packs'], true);
    }

    /**
     * @testdox creates invoice when the user is already a Stripe customer
     *
     * @throws ApiErrorException
     *
     * @ticket #347
     */
    public function testCheckoutAlreadyStripeCustomer(): void
    {
        $user = $this->actingAsGetUser();
        $user->createCustomer();

        /** @var Training $training */
        $training = Training::query()->where('available', '=', true)->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            mutation CheckoutCart($cart: CartInput, $paymentMethodId: String!) {
                checkoutCart(cart: $cart, paymentMethodId: $paymentMethodId) {
                    id
                    status
                    __typename
                }
            }
            GRAPHQL,
            [
                'cart'            => ['trainings' => [$training->id]],
                'paymentMethodId' => 'pm_card_fr',
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertProductsDelivered(['trainings' => [$training->id]]);
    }
}

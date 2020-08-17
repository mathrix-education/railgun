<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Chapter;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use Tests\Utils\CartInputGenerator;
use function array_merge;
use function collect;
use function resolveMorph;
use function trans;

class CheckoutIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * Execute the checkout mutation.
     *
     * @param mixed[] $cartInput       The CartInput.
     * @param string  $paymentMethodId The Stripe {@see PaymentMethod} id.
     */
    protected function executeCheckoutMutation(array $cartInput, string $paymentMethodId): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($cartInput: CartInput!, $paymentMethodId: String!) {
                checkout(cart: $cartInput, paymentMethodId: $paymentMethodId) {
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
            GRAPHQL,
            ['cartInput' => $cartInput, 'paymentMethodId' => $paymentMethodId],
        );
    }

    /**
     * @return mixed[][] The payment methods dataset.
     */
    public function checkoutDataset(): array
    {
        return [
            'products|visa'             => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_visa_credit_fr_cartesBancaires',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_PAID,
                    'paymentIntentStatus' => PaymentIntent::STATUS_SUCCEEDED,
                ],
            ],
            'full|mastercard'           => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plan'            => 'laser_month',
                'paymentMethodId' => 'pm_card_visa_credit_fr_cartesBancaires',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_PAID,
                    'paymentIntentStatus' => PaymentIntent::STATUS_SUCCEEDED,
                ],
            ],
            'products|3DS2'             => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_threeDSecure2Required',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_OPEN,
                    'paymentIntentStatus' => PaymentIntent::STATUS_REQUIRES_ACTION,
                ],
            ],
            'full|3DS2'                 => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plan'            => 'laser_month',
                'paymentMethodId' => 'pm_card_threeDSecure2Required',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_OPEN,
                    'paymentIntentStatus' => PaymentIntent::STATUS_REQUIRES_ACTION,
                ],
            ],
            'products|charge_fail'      => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_chargeDeclined',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.generic'),
                ],
            ],
            'products|incorrect_cvc'    => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_cvcCheckFail',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.incorrect_cvc'),
                ],
            ],
            'products|expired'          => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_chargeDeclinedExpiredCard',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.expired'),
                ],
            ],
            'products|risk_level'       => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_riskLevelHighest',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.generic'),
                ],
            ],
            'products|processing_error' => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plan'            => null,
                'paymentMethodId' => 'pm_card_chargeDeclinedProcessingError',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.generic'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider checkoutDataset
     *
     * @param string[]    $products        The product options ({@see cartInputFactory()})
     * @param string|null $plan            The Mode Machine plan lookup key.
     * @param string      $paymentMethodId The Stripe {@see PaymentMethod} id.
     * @param mixed[]     $expectations    The expectations.
     */
    public function testCheckout(
        array $products,
        ?string $plan,
        string $paymentMethodId,
        array $expectations
    ): void
    {
        $cart = CartInputGenerator::make($products, $plan);

        $user = $this->actingAsGetUser();

        $this->executeCheckoutMutation($cart, $paymentMethodId);

        $acquisitions = collect()
            ->merge(collect($cart['chapters'] ?? [])->map(static fn($id) => [
                'product_type' => resolveMorph(Chapter::class),
                'product_id'   => $id,
            ]))
            ->merge(collect($cart['packs'] ?? [])->map(static fn($id) => [
                'product_type' => resolveMorph(Pack::class),
                'product_id'   => $id,
            ]))
            ->merge(collect($cart['trainings'] ?? [])->map(static fn($id) => [
                'product_type' => resolveMorph(Training::class),
                'product_id'   => $id,
            ]))
            ->map(static fn(array $line) => array_merge($line, ['user_id' => $user->id]));

        $user->refresh();

        if (isset($expectations['invoiceStatus'])) {
            $this->assertNoErrors();
            $this->assertEquals($expectations['invoiceStatus'], $this->getData('status'));
            $this->assertEquals($expectations['paymentIntentStatus'], $this->getData('payment_intent.status'));

            if ($expectations['invoiceStatus'] === Invoice::STATUS_PAID) {
                // Assert that products have been delivered
                $acquisitions->each(fn(array $acquisition) => $this->seeInDatabase('acquisitions', $acquisition));

                if (isset($cart['plan'])) {
                    $this->assertTrue($user->premium);
                }
            }
        } else {
            $acquisitions->each(fn(array $acquisition) => $this->notSeeInDatabase('acquisitions', $acquisition));
            $this->assertFalse($user->premium);
        }

        if (isset($expectations['error_message'])) {
            $this->assertErrorMessage($expectations['error_message']);
        }
    }
}

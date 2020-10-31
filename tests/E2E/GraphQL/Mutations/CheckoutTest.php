<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Stripe\Repositories\PricesRepository;
use Railgun\Models\Chapter;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\CartInputGenerator;
use function array_merge;
use function collect;
use function resolveMorph;
use function trans;

class CheckoutTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @return mixed[][] The payment methods dataset.
     */
    public function checkoutDataset(): array
    {
        return [
            'products|visa'             => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_visa_credit_fr_cartesBancaires',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_PAID,
                    'paymentIntentStatus' => PaymentIntent::STATUS_SUCCEEDED,
                ],
            ],
            'full|mastercard'           => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plans'           => [PricesRepository::PREMIUM_MONTH],
                'paymentMethodId' => 'pm_card_visa_credit_fr_cartesBancaires',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_PAID,
                    'paymentIntentStatus' => PaymentIntent::STATUS_SUCCEEDED,
                ],
            ],
            'products|3ds2'             => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_threeDSecure2Required',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.3ds2'),
                ],
            ],
            'full|3ds2'                 => [
                'products'        => ['chapters', 'packs', 'trainings', 'coupon'],
                'plans'           => [PricesRepository::PREMIUM_MONTH],
                'paymentMethodId' => 'pm_card_threeDSecure2Required',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.3ds2'),
                ],
            ],
            'academy|visa'              => [
                'products'        => [],
                'plans'           => [PricesRepository::ACADEMY_MONTH],
                'paymentMethodId' => 'pm_card_visa_credit_fr_cartesBancaires',
                'expectations'    => [
                    'invoiceStatus'       => Invoice::STATUS_PAID,
                    'paymentIntentStatus' => PaymentIntent::STATUS_SUCCEEDED,
                ],
            ],
            'products|charge_fail'      => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_chargeDeclined',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.generic'),
                ],
            ],
            'products|incorrect_cvc'    => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_cvcCheckFail',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.incorrect_cvc'),
                ],
            ],
            'products|expired'          => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_chargeDeclinedExpiredCard',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.expired'),
                ],
            ],
            'products|risk_level'       => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plans'           => [],
                'paymentMethodId' => 'pm_card_riskLevelHighest',
                'expectations'    => [
                    'error_message' => trans('stripe.errors.card.generic'),
                ],
            ],
            'products|processing_error' => [
                'products'        => ['chapters', 'packs', 'trainings'],
                'plans'           => [],
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
     * @param string[] $products        The product options ({@see cartInputFactory()})
     * @param string[] $plans           Ths plans lookup keys.
     * @param string   $paymentMethodId The Stripe {@see PaymentMethod} id.
     * @param mixed[]  $expectations    The expectations.
     */
    public function testCheckout(
        array $products,
        array $plans,
        string $paymentMethodId,
        array $expectations
    ): void
    {
        $cart = CartInputGenerator::make($products, $plans);

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

                $expectPremium = collect($cart['plans'])
                    ->filter(static fn(string $plan) => Str::contains($plan, ['laser', 'legacy']))
                    ->isNotEmpty();
                $this->assertEquals($expectPremium, $user->premium);

                $expectAcademy = collect($cart['plans'])
                    ->filter(static fn(string $plan) => Str::contains($plan, 'academy'))
                    ->isNotEmpty();
                $this->assertEquals($expectAcademy, $user->academy);
            }
        } else {
            $acquisitions->each(fn(array $acquisition) => $this->notSeeInDatabase('acquisitions', $acquisition));
            $this->assertFalse($user->premium);
            $this->assertFalse($user->academy);
        }

        if (isset($expectations['error_message'])) {
            $this->assertErrorMessage($expectations['error_message']);
        }
    }

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
}

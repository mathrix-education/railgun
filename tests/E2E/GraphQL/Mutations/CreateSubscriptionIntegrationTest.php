<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Exception;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use stdClass;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;
use function strtolower;
use function trans;

/**
 * @testdox Mutation createSubscription
 */
class CreateSubscriptionIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private function executeCreateSubscription(string $planId, string $paymentMethodId): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation ($input: CreateSubscription!) {
                createSubscription(input: $input) {
                    id
                    status
                    latest_invoice {
                        payment_intent {
                            client_secret
                        }
                    }
                    current_period_end
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'planId'          => $planId,
                    'paymentMethodId' => $paymentMethodId,
                ],
            ],
        );
    }

    /**
     * @return mixed[][] The dataset for the ::testCreateSuccess method.
     */
    public function createSuccessDataset(): array
    {
        $paymentMethods = [
            'pm_card_fr'                    => 'ACTIVE',
            'pm_card_threeDSecure2Required' => 'INCOMPLETE',
        ];

        $plans = ['V11_MONTHLY', 'V11_QUARTERLY', 'V11_SEMESTER', 'V11_YEAR'];

        $dataset = [];

        // Build the dataset
        foreach ($paymentMethods as $paymentMethodId => $expectedStatus) {
            foreach ($plans as $planId) {
                $dataset[strtolower($planId) . "|$paymentMethodId => $expectedStatus"] = [
                    $planId,
                    $paymentMethodId,
                    $expectedStatus,
                ];
            }
        }

        return $dataset;
    }

    /**
     * @dataProvider createSuccessDataset
     * @testdox      creates a subscription using plan $planId and token $paymentMethodId
     *
     * @param string $planId          The plan id to use ({@see Plan}).
     * @param string $paymentMethodId The payment method.
     * @param string $expectedStatus  The expected subscription status.
     *
     * @throws Exception
     */
    public function testCreateSubscriptionSuccess(
        string $planId,
        string $paymentMethodId,
        string $expectedStatus
    ): void
    {
        $this->withPermissions();
        $this->executeCreateSubscription($planId, $paymentMethodId);

        /** @var stdClass|Subscription $subscription The response data has the Subscription type */
        $subscription = $this->getData('createSubscription');

        // Check that the status
        $this->assertEquals($expectedStatus, $subscription['status']);

        // Check that the subscription has been saved to the database
        $this->seeInDatabase('subscriptions', [
            'id'     => $subscription['id'],
            'status' => $expectedStatus,
        ]);
    }

    /**
     * @return mixed[][] The dataset for the ::testCreateSubscriptionError method.
     */
    public function createSubscriptionFailsDataset(): array
    {
        $dataset = [];

        $tokens = [
            'pm_card_cvcCheckFail'                    => trans('stripe.errors.card.incorrect_cvc'),
            'pm_card_chargeDeclined'                  => trans('stripe.errors.card.generic'),
            'pm_card_chargeDeclinedInsufficientFunds' => trans('stripe.errors.card.insufficient_funds'),
            'pm_card_chargeDeclinedIncorrectCvc'      => trans('stripe.errors.card.incorrect_cvc'),
            'pm_card_chargeDeclinedExpiredCard'       => trans('stripe.errors.card.expired'),
            'pm_card_chargeDeclinedProcessingError'   => trans('stripe.errors.card.generic'),
        ];

        foreach ($tokens as $paymentMethodId => $expectedMessage) {
            $dataset[$paymentMethodId] = [
                'paymentMethodId' => $paymentMethodId,
                'expectedMessage' => $expectedMessage,
            ];
        }

        return $dataset;
    }

    /**
     * @dataProvider createSubscriptionFailsDataset
     * @testdox      fails to create a subscription using token $paymentMethodId with message "$expectedMessage"
     *
     * @param string $paymentMethodId The payment method.
     * @param string $expectedMessage The expected error message.
     */
    public function testCreateSubscriptionFails(
        string $paymentMethodId,
        string $expectedMessage
    ): void
    {
        $user = $this->withPermissions();
        $this->executeCreateSubscription('V11_MONTHLY', $paymentMethodId);

        $this->assertEquals($expectedMessage, $this->getContent('errors.0.message'));
        $this->assertEquals(0, $user->subscriptions->count()); // No new subscription
    }

    /**
     * @testdox fails to create a subscription because the user is already premium
     */
    public function testFailsAlreadyPremium(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create(['premium_bypass' => true]);
        $this->actingAs($user);
        $this->assertTrue($user->premium);

        $this->executeCreateSubscription('V11_MONTHLY', 'pm_card_fr');

        $this->assertResponseOk();
        $this->assertErrorCode('ALREADY_PREMIUM');
        $this->assertErrorCategory('cart');
        $this->assertErrorMessage(trans('billing.errors.already_premium_you'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Carbon\Carbon;
use Exception;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Models\Plan;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use stdClass;
use Stripe\Subscription as StripeSubscription;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;
use function in_array;
use function trans;

/**
 * @testdox [E2E] Mutation createSubscription
 * @coversNothing
 */
class CreateSubscriptionIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation ($planId: String!, $paymentMethodId: String!) {
        createSubscription(planId: $planId, paymentMethodId: $paymentMethodId) {
            id
            status
            payment_intent_secret
            current_period_end
        }
    }
    GRAPHQL;

    public function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = factory(User::class)->create();
        $this->actingAs($user);
    }

    /**
     * @return array The dataset for the ::testCreateSuccess method.
     */
    public function createSuccessDataset(): array
    {
        $paymentMethods = [
            'pm_card_fr'                    => StripeSubscription::STATUS_ACTIVE,
            'pm_card_threeDSecure2Required' => StripeSubscription::STATUS_INCOMPLETE,
        ];

        $plans = Plan::PLANS;

        $dataset = [];

        // Build the dataset
        foreach ($paymentMethods as $paymentMethodId => $expectedStatus) {
            foreach ($plans as $planId) {
                $dataset["$planId|$paymentMethodId => $expectedStatus"] = [
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
     * @testdox      successfully creates a subscription using plan $planId and token $paymentMethodId.
     *
     * @param string $planId
     * @param string $paymentMethodId
     * @param string $expectedStatus
     *
     * @throws Exception
     */
    public function testCreateSubscriptionSuccess(string $planId, string $paymentMethodId, string $expectedStatus): void
    {
        $this->graphQL(self::QUERY, [
            'planId'          => $planId,
            'paymentMethodId' => $paymentMethodId,
        ]);

        /** @var stdClass|Subscription $subscription The response data has the Subscription type */
        $subscription = $this->getData('createSubscription');

        // Check that the status
        $this->assertEquals($expectedStatus, $subscription->status);
        if ($expectedStatus !== StripeSubscription::STATUS_INCOMPLETE) {
            $this->assertNull($subscription->payment_intent_secret);
        } else {
            $this->assertNotNull($subscription->payment_intent_secret);
        }

        if (in_array($planId, [Plan::ANNUALLY_PLAN, Plan::BIANNUALLY_PLAN], true)) {
            // For annually and biannually plans, check that the anchor is set to the 31st July
            $currentPeriodEnd = new Carbon($subscription->current_period_end);
            $this->assertTrue($currentPeriodEnd->isFuture());
            $this->assertEquals(7, $currentPeriodEnd->month);
            $this->assertEquals(31, $currentPeriodEnd->day);
        }

        // Check that the subscription has been saved to the database
        $this->seeInDatabase('subscriptions', [
            'id'                    => $subscription->id,
            'status'                => $expectedStatus,
            'payment_intent_secret' => $subscription->payment_intent_secret,
        ]);
    }

    /**
     * @return array The dataset for the ::testCreateSubscriptionError method.
     */
    public function createSubscriptionErrorDataset(): array
    {
        $dataset = [];

        $tokens = [
            'pm_card_cvcCheckFail'                    => trans('stripe.errors.card.incorrect_cvc'),
            'pm_card_chargeDeclined'                  => trans('stripe.errors.card.generic'),
            'pm_card_chargeDeclinedInsufficientFunds' => trans('stripe.errors.card.generic'),
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
     * @dataProvider createSubscriptionErrorDataset
     * @testdox      fails to create a subscription using token $paymentMethodId.
     *
     * @param string $paymentMethodId The payment method.
     * @param string $expectedMessage The expected error message.
     */
    public function testCreateSubscriptionError(string $paymentMethodId, string $expectedMessage): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $subscriptionCount = Subscription::query()->count();

        $this->graphQL(self::QUERY, [
            'planId'          => Plan::MONTHLY_PLAN,
            'paymentMethodId' => $paymentMethodId,
        ]);

        $this->assertEquals($expectedMessage, $this->getContent('errors.0.message'));
        $this->assertEquals($subscriptionCount, Subscription::query()->count()); // No new subscription
    }
}

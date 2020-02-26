<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Carbon\Carbon;
use Exception;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\JWT\Testing\JWTUtils;
use Railgun\Billing\SubscriptionUtils;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use stdClass;
use Stripe\Subscription as StripeSubscription;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;
use function in_array;

class CreateSubscriptionIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = '
    mutation ($planId: String!, $paymentMethodId: String!) {
        createSubscription(planId: $planId, paymentMethodId: $paymentMethodId) {
            id
            status
            payment_intent_secret
            current_period_end
        }
    }
    ';

    public function setUp(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        JWTUtils::actingAs($user);
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
        $plans          = SubscriptionUtils::PLANS;

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
     * @testdox      creates a subscription with plan $planId and token $paymentMethodId
     *
     * @param string $planId
     * @param string $paymentMethodId
     * @param string $expectedStatus
     *
     * @throws Exception
     */
    public function testCreateSuccess(string $planId, string $paymentMethodId, string $expectedStatus): void
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

        if (in_array($planId, [SubscriptionUtils::ANNUALLY_PLAN, SubscriptionUtils::BIANNUALLY_PLAN], true)) {
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

    public function createdErrorDataset()
    {
        $tokens = [
            'pm_card_cvcCheckFail',
            'pm_card_chargeCustomerFail',
            'pm_card_riskLevelElevated',
            'pm_card_riskLevelHighest',
            'pm_card_chargeDeclined',
            'pm_card_chargeDeclinedInsufficientFunds',
            'pm_card_chargeDeclinedFraudulent',
            'pm_card_chargeDeclinedIncorrectCvc',
            'pm_card_chargeDeclinedExpiredCard',
            'pm_card_chargeDeclinedProcessingError',
        ];
    }

    public function testCreateFailed(
        string $planId,
        string $paymentMethodId,
        string $expectedMessage
    ): void {
        /** @var User $user */
        $user = factory(User::class)->create();
        JWTUtils::actingAs($user);

        $this->graphQL(self::QUERY, [
            'planId'          => $planId,
            'paymentMethodId' => $paymentMethodId,
        ]);

        $this->assertEquals($expectedMessage, $this->getContent('errors.0.message'));
    }
}

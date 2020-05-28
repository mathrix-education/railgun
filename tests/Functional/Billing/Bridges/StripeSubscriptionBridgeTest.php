<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Bridges;

use Railgun\Billing\Bridges\StripeSubscriptionBridge;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Billing\Bridges\StripeSubscriptionBridge
 */
class StripeSubscriptionBridgeTest extends ApplicationTestCase
{
    /**
     * @testdox converts a subscription where the customer is not embedded
     * @covers ::toEloquent
     *
     * @throws ApiErrorException
     */
    public function testToEloquentNonEmbeddedCustomer(): void
    {
        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = StripeSubscription::all(['limit' => 1])->data[0];

        $subscription = StripeSubscriptionBridge::toEloquent($stripeSubscription);

        $this->assertEquals($stripeSubscription->id, $subscription->id);
        $this->assertMatchesRegularExpression('/cus_/', $subscription->customer_id);
    }

    /**
     * @testdox converts a subscription where the customer is not embedded
     * @covers ::toEloquent
     *
     * @throws ApiErrorException
     */
    public function testToEloquentEmbeddedCustomer(): void
    {
        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = StripeSubscription::all(['limit' => 1, 'expand' => ['data.customer']])->data[0];

        $subscription = StripeSubscriptionBridge::toEloquent($stripeSubscription);

        $this->assertEquals($stripeSubscription->id, $subscription->id);
        $this->assertMatchesRegularExpression('/cus_/', $subscription->customer_id);
    }

    /**
     * @testdox converts a canceled subscription
     * @covers ::toEloquent
     *
     * @throws ApiErrorException
     */
    public function testToEloquentCanceled(): void
    {
        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = StripeSubscription::all([
            'status' => 'canceled',
            'limit'  => 1,
            'expand' => ['data.customer'],
        ])->data[0];

        $subscription = StripeSubscriptionBridge::toEloquent($stripeSubscription);

        $this->assertEquals($stripeSubscription->id, $subscription->id);
        $this->assertNotNull($subscription->canceled_at);
        $this->assertMatchesRegularExpression('/cus_/', $subscription->customer_id);
    }
}

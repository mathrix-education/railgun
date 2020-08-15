<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Carbon\Carbon;
use Railgun\Billing\Models\Plan;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use function count;
use function factory;
use function str_replace;

/**
 * @testdox Model Subscription
 * @coversDefaultClass \Railgun\Models\Subscription
 */
class SubscriptionTest extends ModelTestCase
{
    /**
     * @covers ::user
     */
    public function testUserStripe(): void
    {
        $subscriptionId = $this->faker->regexify('sub_[a-zA-Z0-9]{14}');
        $customerId     = $this->faker->regexify('cus_[a-zA-Z0-9]{14}');
        $anchor         = Carbon::now()->addDays(15);

        $user = factory(User::class)->create(['stripe_id' => $customerId]);

        $subscription = new Subscription([
            'id'                   => $subscriptionId,
            'plan'                 => Plan::MONTHLY_PLAN,
            'customer_id'          => $customerId,
            'status'               => StripeSubscription::STATUS_ACTIVE,
            'current_period_start' => $anchor->subMonth(),
            'current_period_end'   => $anchor,
            'canceled_at'          => null,
        ]);
        $subscription->save();

        $this->assertTrue($subscription->user->is($user));
    }

    /**
     * @covers ::user
     */
    public function testUserBraintree(): void
    {
        $subscriptionId = 'sub_' . str_replace('-', '', $this->faker->uuid);
        $customerId     = 'cus_' . str_replace('-', '', $this->faker->uuid);
        $anchor         = Carbon::now()->addDays(15);

        $user = factory(User::class)->create(['braintree_id' => $customerId]);

        $subscription = new Subscription([
            'id'                   => $subscriptionId,
            'plan'                 => Plan::MONTHLY_PLAN,
            'customer_id'          => $customerId,
            'status'               => StripeSubscription::STATUS_ACTIVE,
            'current_period_start' => $anchor->subMonth(),
            'current_period_end'   => $anchor,
            'canceled_at'          => null,
        ]);
        $subscription->save();

        $this->assertTrue($subscription->user->is($user));
    }

    /**
     * @testWith ["sub_H5jmR5wNf9AOsi", "stripe"]
     *           ["sub_ed4123b4661944c0a8ed1343f1438839", "braintree"]
     * @covers ::getProcessorAttribute
     *
     * @param string $id       The subscription id.
     * @param string $expected The expected processor.
     */
    public function testGetProcessorAttribute(string $id, string $expected): void
    {
        $subscription = new Subscription(['id' => $id]);

        $this->assertEquals($expected, $subscription->processor);
    }

    /**
     * @covers ::getLatestInvoiceAttribute
     *
     * @throws ApiErrorException
     */
    public function testGetLatestInvoice(): void
    {
        $client = $this->app->make(StripeClient::class);

        /** @var StripeSubscription[] $results */
        $results = $client->subscriptions->all([
            'limit'  => 1,
            'status' => StripeSubscription::STATUS_ACTIVE,
        ])->data;

        if (count($results) === 0) {
            $this->markTestSkipped('There is no subscriptions at the moment');

            return;
        }

        $subscription = Subscription::fromStripe($results[0]);

        $this->assertInstanceOf(Invoice::class, $subscription->latest_invoice);
    }
}

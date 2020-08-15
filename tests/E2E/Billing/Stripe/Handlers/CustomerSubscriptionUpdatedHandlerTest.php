<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Handlers;

use Carbon\Carbon;
use Railgun\Billing\Models\Plan;
use Stripe\Subscription;

class CustomerSubscriptionUpdatedHandlerTest extends HandlerTestCase
{
    /**
     * @testWith ["customer.subscription.updated"]
     * @testdox handles asynchronous $event
     *
     * @param string $event The webhook event.
     */
    public function testSubscriptionUpdated(string $event): void
    {
        $subscription = Subscription::constructFrom([
            'object'               => Subscription::OBJECT_NAME,
            'id'                   => $this->faker->regexify('sub_[a-zA-Z0-9]{14}'),
            'plan'                 => [
                'id' => Plan::MONTHLY_PLAN,
            ],
            'customer'             => $this->faker->regexify('cus_[a-zA-Z0-9]{14}'),
            'status'               => Subscription::STATUS_CANCELED,
            'current_period_start' => Carbon::now()->timestamp,
            'current_period_end'   => Carbon::now()->addMonth()->timestamp,
            'created'              => Carbon::now()->timestamp,
        ]);

        $this->callStripeWebhook($event, $subscription);

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));
    }
}

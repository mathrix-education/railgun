<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;

class SubscriptionsWebhookTest extends WebhookTestCase
{
    use DatabaseTransactions;

    /**
     * @testWith ["customer.subscription.updated"]
     * @testdox handles asynchronous $event
     *
     * @param string $event The webhook event.
     *
     * @throws ApiErrorException
     */
    public function testUpsert(string $event): void
    {
        $modeMachine = $this->app->make(ModeMachine::class);

        $subscription = Subscription::constructFrom([
            'object'               => Subscription::OBJECT_NAME,
            'id'                   => $this->faker->regexify('sub_[a-zA-Z0-9]{14}'),
            'plan'                 => [
                'id' => $modeMachine->get('laser_month')->id,
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

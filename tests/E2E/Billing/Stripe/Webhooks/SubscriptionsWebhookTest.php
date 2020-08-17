<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Subscription;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;
use function array_merge;

class SubscriptionsWebhookTest extends WebhookTestCase
{
    use DatabaseTransactions;

    /**
     * Get a valid subscription payload.
     *
     * @see https://stripe.com/docs/api/subscriptions/object
     *
     * @return mixed[] The subscription payload.
     *
     * @throws ApiErrorException
     */
    protected function getSubscriptionPayload(): array
    {
        $modeMachine = $this->app->make(ModeMachine::class);

        return [
            'object'               => StripeSubscription::OBJECT_NAME,
            'id'                   => $this->faker->regexify('sub_[a-zA-Z0-9]{14}'),
            'plan'                 => [
                'id' => $modeMachine->get('laser_month')->id,
            ],
            'customer'             => $this->faker->regexify('cus_[a-zA-Z0-9]{14}'),
            'status'               => StripeSubscription::STATUS_CANCELED,
            'current_period_start' => Carbon::now()->timestamp,
            'current_period_end'   => Carbon::now()->addMonth()->timestamp,
            'created'              => Carbon::now()->timestamp,
        ];
    }

    /**
     * @throws ApiErrorException
     */
    public function testUpsert(): void
    {
        $payload = array_merge(
            $this->getSubscriptionPayload(),
            ['status' => StripeSubscription::STATUS_CANCELED],
        );

        $subscription = StripeSubscription::constructFrom($payload);

        $this->callStripeWebhook(Event::CUSTOMER_SUBSCRIPTION_UPDATED, $subscription);

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));

        $this->seeInDatabase('subscriptions', Arr::only($payload, ['id', 'status', 'customer_id']));
    }

    /**
     * @throws ApiErrorException
     */
    public function testDelete(): void
    {
        $payload = array_merge(
            $this->getSubscriptionPayload(),
            ['status' => StripeSubscription::STATUS_ACTIVE],
        );

        $subscription = StripeSubscription::constructFrom($payload);
        Subscription::fromStripe($subscription)->save();

        $this->seeInDatabase('subscriptions', Arr::only($payload, ['id', 'status', 'customer_id']));

        $this->callStripeWebhook(Event::CUSTOMER_SUBSCRIPTION_DELETED, $subscription);

        $this->assertResponseOk();
        $this->assertEquals('Subscription deleted', $this->getContent('message'));

        $this->notSeeInDatabase('subscriptions', Arr::only($payload, ['id', 'status', 'customer_id']));
    }
}

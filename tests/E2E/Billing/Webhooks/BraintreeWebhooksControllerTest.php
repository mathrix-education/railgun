<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

use Braintree\Gateway;
use Braintree\WebhookNotification;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function str_replace;
use function uuid_create;

/**
 * @coversDefaultClass \Railgun\Billing\Webhooks\BraintreeWebhooksController
 */
class BraintreeWebhooksControllerTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::handle
     */
    public function testSubscriptionCanceled(): void
    {
        $subscription = new Subscription([
            'id'                   => 'sub_' . str_replace('-', '', uuid_create()),
            'plan'                 => 'v4_monthly',
            'customer_id'          => 'cus_' . str_replace('-', '', uuid_create()),
            'status'               => StripeSubscription::STATUS_ACTIVE,
            'current_period_start' => Carbon::now(),
            'current_period_end'   => Carbon::now()->addMonth(),
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
            'canceled_at'          => null,
        ]);
        $subscription->save();

        $payload = $this->app->make(Gateway::class)
            ->webhookTesting()
            ->sampleNotification(WebhookNotification::SUBSCRIPTION_CANCELED, $subscription->id);

        $this->post('/webhooks/braintree', $payload);

        $this->assertResponseOk();
        $this->seeInDatabase('subscriptions', [
            'id'     => $subscription->id,
            'status' => StripeSubscription::STATUS_CANCELED,
        ]);
        $this->assertNotNull($subscription->refresh()->canceled_at);
    }
}

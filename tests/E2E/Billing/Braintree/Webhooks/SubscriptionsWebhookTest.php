<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Braintree\Webhooks;

use Braintree\Gateway;
use Braintree\WebhookNotification;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use Tests\Utils\BraintreeGenerator;

class SubscriptionsWebhookTest extends WebhooksTestCase
{
    use DatabaseTransactions;

    protected Gateway $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = $this->app->make(Gateway::class);
    }

    public function testWentActive(): void
    {
        $context = BraintreeGenerator::subscription();
        /** @var Subscription $subscription */
        $subscription = $context['subscription'];
        $subscription->delete();

        $this->callBraintreeWebhook(
            WebhookNotification::SUBSCRIPTION_WENT_ACTIVE,
            $this->gateway->subscription()->find($subscription->id),
        );

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));
        $this->seeInDatabase('subscriptions', [
            'id'     => $subscription->id,
            'status' => StripeSubscription::STATUS_ACTIVE,
        ]);
    }

    public function testCancel(): void
    {
        $context = BraintreeGenerator::subscription();
        /** @var Subscription $subscription */
        $subscription = $context['subscription'];
        $subscription->delete();

        $this->gateway->subscription()->cancel($subscription->id);

        $this->callBraintreeWebhook(
            WebhookNotification::SUBSCRIPTION_CANCELED,
            $this->gateway->subscription()->find($subscription->id),
        );

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));
        $this->seeInDatabase('subscriptions', [
            'id'     => $subscription->id,
            'status' => StripeSubscription::STATUS_CANCELED,
        ]);
    }
}

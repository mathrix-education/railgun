<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stripe\Webhooks;

use App\Services\Stripe\Webhooks\PaymentIntentSucceededWebhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use Tests\BaseTestCase;
use function count;

/**
 * @coversDefaultClass \App\Services\Stripe\Webhooks\PaymentIntentSucceededWebhook
 */
class PaymentIntentSucceededWebhookTest extends BaseTestCase
{
    /**
     * @throws ApiErrorException
     *
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $subscriptions = Subscription::all([
            'status' => Subscription::STATUS_ACTIVE,
            'limit'  => 1,
            'expand' => ['data.latest_invoice.payment_intent'],
        ])->data;

        if (count($subscriptions) === 0) {
            $this->markTestSkipped('There is not active subscription in the test environment');
        }

        /** @var Subscription $subscription */
        $subscription = $subscriptions[0];
        /** @var Invoice $latestInvoice */
        $latestInvoice = $subscription->latest_invoice;
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $latestInvoice->payment_intent;

        $webhook = new PaymentIntentSucceededWebhook();
        $webhook->handle($paymentIntent);
        $paymentIntent->refresh();

        $expectedDescription = "{$subscription->plan->nickname} subscription creation";

        $this->assertEquals($expectedDescription, $paymentIntent->description);
    }
}

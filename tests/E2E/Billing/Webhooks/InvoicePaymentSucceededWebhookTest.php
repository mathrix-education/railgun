<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

use Illuminate\Support\Facades\Bus;
use Railgun\Jobs\DeliverProducts;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\User;
use Stripe\Event;
use Stripe\Invoice;
use Stripe\StripeObject;
use function factory;

/**
 * @testdox Webhook "invoice.payment_succeeded"
 */
class InvoicePaymentSucceededWebhookTest extends WebhookTestCase
{
    /**
     * @testdox handles asynchronous cart checkout
     */
    public function testHandleAsyncCartCheckout(): void
    {
        Bus::fake();

        /** @var User $user */
        $user = factory(User::class)->create(['stripe_id' => $this->faker->md5]);

        $invoice = Invoice::constructFrom([
            'id'             => $this->faker->md5,
            'object'         => 'invoice',
            'billing_reason' => Invoice::BILLING_REASON_MANUAL,
            'customer'       => $user->stripe_id,
            'metadata'       => StripeObject::constructFrom(['type' => Cart::IDENTIFIER]),
        ]);

        $this->callStripeWebhook(Event::INVOICE_PAYMENT_SUCCEEDED, $invoice);

        $this->assertResponseOk();
        Bus::assertDispatched(
            DeliverProducts::class,
            static fn(DeliverProducts $job): bool => $invoice->id === $job->invoice->id,
        );
    }

    /**
     * @testdox handles asynchronous subscription creation
     */
    public function testHandleAsyncSubscriptionCreation(): void
    {
        Bus::fake();

        $subscriptionId = $this->faker->md5;
        /** @var User $user */
        $user = factory(User::class)->create(['stripe_id' => $this->faker->md5]);

        $invoice = Invoice::constructFrom([
            'id'             => $this->faker->md5,
            'object'         => 'invoice',
            'billing_reason' => Invoice::BILLING_REASON_SUBSCRIPTION_CREATE,
            'customer'       => $user->stripe_id,
            'subscription'   => $subscriptionId,
        ]);

        $this->callStripeWebhook(Event::INVOICE_PAYMENT_SUCCEEDED, $invoice);

        $this->assertResponseOk();
        Bus::assertDispatched(
            UpsertSubscription::class,
            static fn(UpsertSubscription $job): bool => $job->stripeSubscription === $subscriptionId,
        );
    }
}

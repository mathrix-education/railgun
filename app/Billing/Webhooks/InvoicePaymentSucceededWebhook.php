<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Support\Str;
use Railgun\Billing\Cart\Cart;
use Railgun\Jobs\DeliverProducts;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Event;
use Stripe\Invoice;
use function dispatch;

/**
 * Handle invoice.payment_succeeded Stripe event.
 */
trait InvoicePaymentSucceededWebhook
{
    /**
     * @param Event $event The Stripe Event.
     *
     * @return string The response content.
     *
     * @noinspection PhpUnused
     */
    public function handleInvoicePaymentSucceeded(Event $event): string
    {
        /** @var Invoice $invoice */
        $invoice = $event->data;

        if (Str::contains($invoice->billing_reason, 'subscription')) {
            // This is a subscription invoice
            dispatch(new UpsertSubscription($invoice->subscription));
        }

        if (
            $invoice->billing_reason === Invoice::BILLING_REASON_MANUAL
            && $invoice->metadata->type === Cart::IDENTIFIER
        ) {
            dispatch(new DeliverProducts($invoice));
        }

        return 'OK';
    }
}

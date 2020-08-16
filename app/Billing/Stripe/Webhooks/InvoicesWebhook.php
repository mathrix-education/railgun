<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Webhooks;

use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Billing\Cart\Cart;
use Railgun\Jobs\DispatchCart;
use Railgun\Models\User;
use RuntimeException;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use function app;
use function is_string;

/**
 * Handler for the Stripe events:
 * - invoice.paid
 * - invoice.payment_succeeded
 *
 * @see Event::INVOICE_PAID
 * @see Event::INVOICE_PAYMENT_SUCCEEDED
 */
class InvoicesWebhook
{
    /**
     * @param Dispatcher $dispatcher The bus dispatcher.
     * @param Event      $event      The Stripe event.
     * @param Invoice    $invoice    The Invoice object.
     *
     * @return mixed[]
     *
     * @throws ApiErrorException
     */
    public function process(Dispatcher $dispatcher, Event $event, Invoice $invoice): array
    {
        if (!isset($invoice->metadata['identifier']) || $invoice->metadata['identifier'] === Cart::IDENTIFIER) {
            return [
                'message' => 'Invoice ignored',
                'event'   => $event->jsonSerialize(),
            ];
        }

        $customer = is_string($invoice->customer) ? $invoice->customer : $invoice->customer->id;
        $user     = User::query()->where('stripe_id', '=', $customer)->first();

        if ($user && !app()->environment('production')) {
            throw new RuntimeException("Stripe customer $customer not found");
        }

        $cart = Cart::fromMetadata($invoice->metadata->toArray());

        $dispatcher->dispatchNow(new DispatchCart($cart, $user->id, $invoice->id));

        return [
            'message' => 'Invoice handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}

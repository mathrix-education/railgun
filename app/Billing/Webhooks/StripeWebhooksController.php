<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Railgun\Billing\Cart\Cart;
use Railgun\Billing\Exceptions\UnsupportedEventException;
use Railgun\Jobs\DeliverProducts;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\Webhook;
use function array_key_exists;
use function config;
use function dispatch;
use function method_exists;
use function str_replace;

/**
 * Handle Stripe Events.
 */
class StripeWebhooksController extends Controller
{
    private const EVENT_MAP = [
        // Event::CUSTOMER_SUBSCRIPTION_CREATED, // we probably do not want this
        Event::CUSTOMER_SUBSCRIPTION_UPDATED => 'handleCustomerSubscription',
        Event::CUSTOMER_SUBSCRIPTION_DELETED => 'handleCustomerSubscription',
    ];

    /**
     * Get the handler method based on the Stripe {@see Event} type.
     *
     * @param string $eventType The Stripe {@see Event} type
     *
     * @return string The associated handler method name.
     */
    private function getHandler(string $eventType): string
    {
        if (array_key_exists($eventType, self::EVENT_MAP)) {
            // Check if the event has been declared in the map.
            return self::EVENT_MAP[$eventType];
        }

        // Fallback to default handler
        $method = 'handle' . Str::studly(str_replace('.', '_', $eventType));

        if (!method_exists($this, $method)) {
            throw new UnsupportedEventException($eventType);
        }

        return $method;
    }

    /**
     * POST /webhooks/stripe
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return mixed The event webhook response.
     *
     * @throws SignatureVerificationException
     */
    public function handle(Request $request)
    {
        $signature = $request->header('Stripe-Signature');
        $payload   = $request->getContent();

        $event = Webhook::constructEvent(
            $payload,
            $signature,
            (string)config('services.stripe.webhook.secret'),
            config('services.stripe.webhook.tolerance'),
        );

        return $this->{$this->getHandler($event->type)}($event);
    }

    /**
     * Handle invoice.payment_succeeded Stripe event.
     *
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

            return 'SUBSCRIPTION OK';
        }

        if (
            $invoice->billing_reason === Invoice::BILLING_REASON_MANUAL
            && $invoice->metadata->type === Cart::IDENTIFIER
        ) {
            dispatch(new DeliverProducts($invoice));

            return 'CART OK';
        }

        return 'IGNORED';
    }

    /**
     * Handles "customer.subscription.*" Stripe events.
     *
     * @param Event $event The Stripe Event.
     *
     * @return string The response to send to Stripe.
     *
     * @noinspection PhpUnused
     */
    public function handleCustomerSubscription(Event $event): string
    {
        dispatch(new UpsertSubscription($event->data));

        return 'SUBSCRIPTION OK';
    }
}

<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Railgun\Billing\Stripe\Checkout\Cart;
use Railgun\Billing\Exceptions\UnsupportedEventException;
use Railgun\Jobs\DeliverProducts;
use Railgun\Jobs\UpsertSubscription;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\Webhook;
use function array_key_exists;
use function config;
use function dispatch_now;
use function method_exists;
use function str_replace;

/**
 * Handle Stripe Events.
 */
class StripeWebhooksController extends Controller
{
    private const IGNORED_MESSAGE = 'Not handled';
    private const EVENT_MAP       = [
        Event::CUSTOMER_SUBSCRIPTION_UPDATED => 'handleCustomerSubscription',
        Event::CUSTOMER_SUBSCRIPTION_DELETED => 'handleCustomerSubscription',
    ];

    /**
     * Get the handler method based on the Stripe {@see Event} type.
     *
     * @param string|null $eventType The Stripe {@see Event} type
     *
     * @return string The associated handler method name.
     */
    private function getHandler(string $eventType): ?string
    {
        if (array_key_exists($eventType, self::EVENT_MAP)) {
            // Check if the event has been declared in the map.
            return self::EVENT_MAP[$eventType];
        }

        // Fallback to default handler
        $method = 'handle' . Str::studly(str_replace('.', '_', $eventType));

        if (!method_exists($this, $method)) {
            return null;
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
        $profile   = $request->query('profile', 'primary');
        $secret    = $profile === 'primary'
            ? config('stripe.primary.webhooks_secret')
            : config('stripe.secondary.webhooks_secret');
        $tolerance = $profile === 'primary'
            ? config('stripe.primary.tolerance')
            : config('stripe.secondary.tolerance');

        $event = Webhook::constructEvent(
            $payload,
            $signature,
            $secret,
            $tolerance,
        );

        $handler = $this->getHandler($event->type);

        if ($handler === null) {
            throw new UnsupportedEventException($event);
        }

        return $this->{$handler}($event);
    }

    /**
     * Handle invoice.payment_succeeded Stripe event.
     *
     * @param Event $event The Stripe Event.
     *
     * @return mixed[] The response content.
     *
     * @noinspection PhpUnused
     */
    public function handleInvoicePaymentSucceeded(Event $event): array
    {
        /** @var Invoice $invoice */
        $invoice = $event->data->object;
        $message = null;

        if (Str::contains($invoice->billing_reason, 'subscription')) {
            // This is a subscription invoice
            dispatch_now(new UpsertSubscription($invoice->subscription));

            $message = 'Subscription invoice payment handled';
        }

        if (
            $invoice->billing_reason === Invoice::BILLING_REASON_MANUAL
            && $invoice->metadata->type === Cart::IDENTIFIER
        ) {
            dispatch_now(new DeliverProducts($invoice));

            $message = 'Cart invoice payment handled';
        }

        return [
            'message' => $message ?? self::IGNORED_MESSAGE,
            'event'   => $event,
        ];
    }

    /**
     * Handles "customer.subscription.*" Stripe events.
     *
     * @param Event $event The Stripe Event.
     *
     * @return mixed[] The response to send to Stripe.
     *
     * @noinspection PhpUnused
     */
    public function handleCustomerSubscription(Event $event): array
    {
        dispatch_now(new UpsertSubscription($event->data->object));

        return [
            'message' => 'Subscription handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}

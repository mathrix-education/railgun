<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Railgun\Billing\Exceptions\UnsupportedEventException;
use ReflectionException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use function config;
use function method_exists;
use function str_replace;

/**
 * Handle Stripe Events.
 */
class StripeWebhooksController extends Controller
{
    use InvoicePaymentSucceededWebhook;

    /**
     * POST /webhooks/stripe
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return mixed The event webhook response.
     *
     * @throws ReflectionException
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

        $handler = 'handle' . Str::studly(str_replace('.', '_', $event->type));

        if (!method_exists($this, $handler)) {
            throw new UnsupportedEventException($event->type);
        }

        return $this->{$handler}($event);
    }
}

<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

use Carbon\Carbon;
use JsonException;
use Stripe\Event;
use Stripe\StripeObject;
use Stripe\WebhookSignature;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function assert;
use function config;
use function hash_hmac;
use function is_int;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * Base class for testing Stripe webhooks.
 */
abstract class WebhookTestCase extends GraphQLIntegrationTestCase
{
    /**
     * Do a Stripe webhook call using a given payload.
     *
     * @param string       $type The event type {@see Event}
     * @param StripeObject $data The event payload.
     */
    protected function callStripeWebhook(string $type, StripeObject $data): void
    {
        $event = $this->buildEvent($type, $data);

        try {
            $payload = json_encode($event->jsonSerialize(), JSON_THROW_ON_ERROR, 512);
        } catch (JsonException $exception) {
            $this->fail($exception->getMessage());
            return;
        }

        $this->postStripe($payload, $this->sign($payload));
    }

    /**
     * POST /webhooks/stripe
     *
     * @param string $payload   The JSON-encoded payload.
     * @param string $signature The payload signature.
     */
    protected function postStripe(string $payload, string $signature): void
    {
        $this->call(
            'post',
            config('services.stripe.webhook.route'),
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $signature],
            $payload,
        );
    }

    /**
     * Build the event.
     *
     * @param string       $type The event type {@see buildEvent}
     * @param StripeObject $data The event payload.
     *
     * @return Event The Stripe event.
     */
    public function buildEvent(string $type, StripeObject $data): Event
    {
        return Event::constructFrom([
            'type' => $type,
            'data' => $data,
        ]);
    }

    /**
     * Sign a payload from Stripe webhooks.
     *
     * @param string            $payload   The JSON-encoded payload.
     * @param Carbon|int|string $timestamp The signature timestamp.
     *
     * @return string The signature of the (payload|timestamp).
     */
    protected function sign(string $payload, $timestamp = null): string
    {
        if ($timestamp instanceof Carbon) {
            $timestamp = $timestamp->getTimestamp();
        } elseif ($timestamp === null) {
            $timestamp = Carbon::now()->getTimestamp();
        } else {
            $timestamp = Carbon::make($timestamp)->getTimestamp();
        }

        assert(is_int($timestamp));

        $scheme    = WebhookSignature::EXPECTED_SCHEME;
        $signature = hash_hmac(
            'sha256',
            "{$timestamp}.{$payload}",
            (string)config('services.stripe.webhook.secret'),
        );

        return "t={$timestamp},{$scheme}={$signature}";
    }
}

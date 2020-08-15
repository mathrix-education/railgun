<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

use JsonException;
use Railgun\Billing\Stripe\StripeWebhook;
use Stripe\Event;
use Stripe\StripeObject;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function config;
use function json_encode;
use function str_replace;
use const JSON_THROW_ON_ERROR;

/**
 * Base class for testing Stripe webhooks.
 */
abstract class WebhookTestCase extends GraphQLIntegrationTestCase
{
    protected StripeWebhook $webhook;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhook = $this->app->make(StripeWebhook::class);
    }

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

        $this->postStripe($payload, $this->webhook->sign($payload));
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
            str_replace(
                '{account}',
                config('stripe.defaults.webhooks'),
                config('stripe.webhooks_route.uri'),
            ),
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
            'data' => ['object' => $data],
        ]);
    }
}

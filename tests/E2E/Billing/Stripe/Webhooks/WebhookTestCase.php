<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use JsonException;
use Railgun\Billing\Stripe\StripeWebhook;
use Stripe\Event;
use Stripe\StripeObject;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class WebhookTestCase extends GraphQLIntegrationTestCase
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
        $event = Event::constructFrom([
            'type' => $type,
            'data' => ['object' => $data],
        ]);

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
            $this->webhook->route(),
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $signature],
            $payload,
        );
    }
}

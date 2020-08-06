<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Handlers;

use JsonException;
use Railgun\Billing\Stripe\StripeWebhookRegistry;
use Stripe\Event;
use Stripe\StripeObject;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class HandlerTestCase extends GraphQLIntegrationTestCase
{
    protected StripeWebhookRegistry $registry;

    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->app->make(StripeWebhookRegistry::class);
    }

    /**
     * Do a Stripe webhook call using a given payload.
     *
     * @param string       $type    The event type {@see Event}
     * @param StripeObject $data    The event payload.
     * @param string|null  $account The webhook configuration name.
     */
    protected function callStripeWebhook(
        string $type,
        StripeObject $data,
        ?string $account = null
    ): void
    {
        $webhook = $this->registry->make($account);

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

        $this->call(
            'post',
            $webhook->route(),
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $webhook->sign($payload)],
            $payload,
        );
    }
}

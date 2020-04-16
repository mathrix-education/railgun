<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

use Stripe\StripeObject;
use function trans;

/**
 * @testdox Webhooks Controller
 */
class WebhooksControllerTest extends WebhookTestCase
{
    /**
     * @testdox fails to handle the event when signature is invalid.
     */
    public function testInvalidSignature(): void
    {
        $this->postStripe($this->faker->md5, $this->faker->md5);

        $this->assertResponseStatus(400);
    }

    /**
     * @testdox fails to handle an unsupported event.
     */
    public function testUnsupportedEvent(): void
    {
        $event = $this->faker->uuid;
        $this->callStripeWebhook($event, StripeObject::constructFrom([]));

        $this->assertResponseStatus(400);
        $this->assertEquals('UNSUPPORTED_EVENT', $this->getContent('extensions.code'));
        $this->assertEquals('billing', $this->getContent('extensions.category'));
        $this->assertEquals(
            trans('billing.errors.unsupported_event', ['eventType' => $event]),
            $this->getContent('message'),
        );
    }
}

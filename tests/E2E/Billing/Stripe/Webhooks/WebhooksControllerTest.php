<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Stripe\StripeObject;
use function trans;

class WebhooksControllerTest extends WebhookTestCase
{
    use DatabaseTransactions;

    public function testInvalidSignature(): void
    {
        $this->postStripe(
            $this->faker->md5,
            $this->webhook->sign($this->faker->md5),
        );

        $this->assertResponseStatus(400);
    }

    public function testUnsupportedEvent(): void
    {
        $this->callStripeWebhook('unsupported', StripeObject::constructFrom([]));

        $this->assertResponseStatus(400);
        $this->assertEquals('UNSUPPORTED_EVENT', $this->getContent('extensions.code'));
        $this->assertEquals('billing', $this->getContent('extensions.category'));
        $this->assertEquals(
            trans('billing.errors.unsupported_event', ['eventType' => 'unsupported']),
            $this->getContent('message'),
        );
    }
}

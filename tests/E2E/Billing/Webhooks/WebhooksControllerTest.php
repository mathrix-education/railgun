<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Webhooks;

/**
 * @testdox [E2E] Webhooks Controller
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
}

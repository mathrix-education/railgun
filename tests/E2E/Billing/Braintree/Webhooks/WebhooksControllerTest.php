<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Braintree\Webhooks;

use Braintree\Subscription;
use Laravel\Lumen\Testing\DatabaseTransactions;
use function trans;

class WebhooksControllerTest extends WebhooksTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox fails to handle the event when signature is invalid
     */
    public function testInvalidSignature(): void
    {
        $this->postBraintree(
            $this->faker->md5,
            $this->sign($this->faker->md5),
        );

        $this->assertResponseStatus(400);
    }

    /**
     * @testdox fails to handle an unsupported event
     */
    public function testUnsupportedEvent(): void
    {
        $this->callBraintreeWebhook('unsupported', Subscription::factory([]));

        $this->assertResponseStatus(400);
        $this->assertEquals('UNSUPPORTED_EVENT', $this->getContent('extensions.code'));
        $this->assertEquals('billing', $this->getContent('extensions.category'));
        $this->assertEquals(
            trans('billing.errors.unsupported_event', ['eventType' => 'unsupported']),
            $this->getContent('message'),
        );
    }
}

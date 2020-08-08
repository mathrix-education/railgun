<?php

namespace Tests\Functional\Billing\Stripe;

use Railgun\Billing\Stripe\StripeWebhooksController;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Billing\Stripe\StripeWebhooksController
 */
class StripeWebhooksControllerTest extends ApplicationTestCase
{
    public function testGetHandler(): void
    {
        $instance = new StripeWebhooksController();
    }
}

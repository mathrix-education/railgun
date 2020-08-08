<?php

namespace Tests\Functional\Billing\Stripe;

use Railgun\Billing\Stripe\EventsController;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\Billing\Stripe\EventsController
 */
class StripeWebhooksControllerTest extends ApplicationTestCase
{
    public function testGetHandler(): void
    {
        $instance = new EventsController();
    }
}

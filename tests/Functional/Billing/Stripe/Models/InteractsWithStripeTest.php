<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Stripe\Models;

use Railgun\Billing\Stripe\Models\InteractsWithStripe;
use Railgun\Models\BaseModel;
use Stripe\StripeClient;
use Tests\ApplicationTestCase;

/**
 * @covers \Railgun\Billing\Stripe\Models\InteractsWithStripe
 */
class InteractsWithStripeTest extends ApplicationTestCase
{
    public function testStripeClient(): void
    {
        $instance = new class extends BaseModel {
            use InteractsWithStripe;

            public function getStripeClient(): StripeClient
            {
                return $this->client;
            }
        };

        $this->assertEquals(
            $this->app->make(StripeClient::class),
            $instance->getStripeClient(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stripe\Webhooks;

use stdClass;
use Tests\APIStatelessTestCase;

class SetupIntentSucceededWebhookTest extends APIStatelessTestCase
{
    public function testHandle()
    {
        $mock = new stdClass();
        $mock->metadata = [

        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use Tests\APIStatelessTestCase;

class PlansTest extends APIStatelessTestCase
{
    /**
     * GET /plans
     */
    public function testAll(): void
    {
        $this->json('get', '/plans');

        $this->assertOpenAPIResponse($this->response);
    }
}

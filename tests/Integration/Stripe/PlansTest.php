<?php

namespace Tests\Integration\Stripe;

use Tests\APIStatelessTestCase;

/**
 * Class PlansTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class PlansTest extends APIStatelessTestCase
{
    /**
     * GET /plans
     */
    public function testAll(): void
    {
        $this->json("get", "/plans");
        $this->assertOpenAPIResponse($this->response);
    }
}

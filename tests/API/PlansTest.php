<?php

namespace Tests\API;

use App\Models\User;
use Tests\APIStatelessTestCase;

/**
 * Class PlansTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PlansTest extends APIStatelessTestCase
{
    /**
     * GET /plans
     */
    public function testAll()
    {
        $this->json("get", "/plans");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /plans
     */
    public function testGet()
    {
        $planId = $this->faker->randomElement(User::BRAINTREE_PLANS);
        $this->json("get", "/plans/$planId");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }
}

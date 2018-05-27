<?php
namespace Tests\API\Stripe;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\API\ScopeTestCase;

/**
 * Class StripeAsRegisteredTest
 * Test quotes as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 1.0.0
 * @package Tests\Quotes
 */
class StripeAsRegisteredTest extends ScopeTestCase
{
    /**
     * GET /stripe/estimate
     *
     * @throws \Exception
     */
    public function testEstimate()
    {
        $this->call("get", "/stripe/estimate/v3_annually");
        $response = $this->getResponseData() ;

        $this->assertHttp200Success();
        $this->assertTrue(isset($response->price));
        $this->assertTrue(isset($response->end_date));
    }
}

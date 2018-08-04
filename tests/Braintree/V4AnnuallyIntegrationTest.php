<?php
namespace Tests\Braintree;

use App\Models\User;
use Laravel\Passport\Passport;

/**
 * Class V4AnnuallyIntegrationTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class V4AnnuallyIntegrationTest extends BraintreeTestCase
{
    /**
     * POST /braintree/subscriptions
     *
     * @throws \App\Exceptions\Http400InvalidRequestException
     */
    public function testSubscribeAnnuallyAsNonCustomer()
    {
        $customer = User::where("type", "=", "registered")
            ->whereNull("braintree_id")
            ->firstOrFail();
        Passport::actingAs($customer, ["registered"]);

        $this->json("post", "/subscriptions", [
            "plan" => "v4_annually",
            "payment_method_nonce" => "fake-valid-visa-nonce"
        ]);

        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertNotInDatabase("users", [
            "id" => $customer->id,
            "braintree_id" => null
        ]);
        $this->assertTrue($customer->hasBraintreeSubscription("v4_annually"));
        $this->assertTrue($responseData->premium);
    }
}

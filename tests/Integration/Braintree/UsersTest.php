<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Test\Nonces;
use Exception;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;
use Tests\Utils\BraintreeUtils;

/**
 * Class UsersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class UsersTest extends APIStatelessTestCase
{
    /**
     * GET /users/{userId}/subscriptions
     *
     * @throws Exception
     */
    public function testSubscriptions()
    {
        // Create the Braintree customer
        $this->requester = factory(User::class)->create();
        BraintreeUtils::createCustomer($this->requester, Nonces::$transactableVisa);

        /** @var Successful|Error $result */
        $result = gateway()->subscription()->create([
            "id" => "sub_" . str_replace("-", "", $this->faker->uuid),
            "paymentMethodToken" => $this->requester->asBraintreeCustomer()->defaultPaymentMethod()->token,
            "planId" => "v4_monthly"
        ]);

        $this->requester->invalidate("braintree.%");

        if (!$result->success) {
            $this->fail(implode("\n", $result->errors->deepAll()));
        }

        JWT::actingAs($this->requester);
        $this->json("get", "/users/{$this->requester->id}/subscriptions");

        $this->debug();
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}

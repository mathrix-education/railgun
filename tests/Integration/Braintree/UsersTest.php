<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Test\Nonces;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

class UsersTest extends APIStatelessTestCase
{
    public function testSubscriptions()
    {
        /** @var User $user */
        $user = $this->getFactoryBuilder([])->create();
        $user->createBraintreeCustomer(Nonces::$transactableVisa);

        /** @var Successful|Error $result */
        $result = gateway()->subscription()->create([
            "id" => "sub_" . str_replace("-", "", $this->faker->uuid),
            "paymentMethodToken" => $user->asBraintreeCustomer()->defaultPaymentMethod()->token,
            "planId" => "v4_monthly"
        ]);

        $user->forgetBraintreeCustomerCache();

        if (!$result->success) {
            $this->fail(implode("\n", $result->errors->deepAll()));
        }

        JWT::actingAs($user);
        $this->json("get", "/users/{$user->id}/subscriptions");

        $this->debug();
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}

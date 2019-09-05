<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;

class UsersTest extends APIStatelessTestCase
{
    public function testLol()
    {
        $customer = Customer::create([
            "email" => $this->faker->email,
            "source" => "tok_fr"
        ]);

        Customer::createSource($customer->id, [
            "source" => "tok_es"
        ]);

        $customer = Customer::retrieve($customer->id);

        dd($customer->sources->data);
    }


    public function testSubscriptions()
    {
        /** @var User $user */
        $user = $this->getFactoryBuilder([])->create();
        $user->createStripeCustomer("tok_fr");

        $subscription = Subscription::create([
            "customer" => $user->stripe_id,
            "default_source" => $user->getStripeCustomer()->default_source,
            "items" => [
                [
                    "plan" => SubscriptionUtils::MONTHLY_PLAN,
                ]
            ]
        ]);

        $user->forgetStripeCustomerCache();
        JWT::actingAs($user);
        $this->json("get", "/users/{$user->id}/subscriptions");

        $this->debug();
        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}

<?php

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\Models\StripeCustomer;
use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;

/**
 * Class UsersTest.
 * Test UserController while using Stripe.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class UsersTest extends APIStatelessTestCase
{
    /**
     * GET /users/{userId}/subscriptions
     */
    public function testSubscriptions()
    {
        /** @var User $user */
        $user = $this->getFactoryBuilder([])->create();
        $user->createStripeCustomer("tok_fr");

        Subscription::create([
            "customer" => $user->stripe_id,
            "default_source" => $user->asStripeCustomer()->default_source,
            "items" => [
                [
                    "plan" => SubscriptionUtils::MONTHLY_PLAN,
                ]
            ]
        ]);

        $user->invalidate(StripeCustomer::$StripeSubscriptionsCacheKey);

        JWT::actingAs($user);
        $this->json("get", "/users/{$user->id}/subscriptions");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}

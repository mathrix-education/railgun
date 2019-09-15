<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\Models\StripeCustomer;
use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;
use function factory;

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
     * GET /users/{userId}/cards
     */
    public function testCards()
    {
        // Create a new user, add two cards
        $this->requester = factory(User::class)->create();
        $this->requester->createStripeCustomer('tok_fr');
        $this->requester->addSource('tok_es');
        JWT::actingAs($this->requester);

        $this->json('get', '/users/{$this->requester->id}/cards');

        $this->assertResponseStatus(200);
        $this->assertOpenAPIResponse($this->response);
    }

    /**
     * GET /users/{userId}/subscriptions
     */
    public function testSubscriptions()
    {
        /** @var User $user */
        $user = $this->getFactoryBuilder([])->create();
        $user->createStripeCustomer('tok_fr');

        Subscription::create([
            'customer' => $user->stripe_id,
            'default_source' => $user->asStripeCustomer()->default_source,
            'items' => [
                [
                    'plan' => SubscriptionUtils::MONTHLY_PLAN,
                ],
            ],
        ]);

        $user->invalidate(StripeCustomer::$StripeSubscriptionsCacheKey);

        JWT::actingAs($user);
        $this->json('get', "/users/{$user->id}/subscriptions");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }
}

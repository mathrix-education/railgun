<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;
use function factory;
use function json_decode;

class SubscriptionCardTest extends APIStatelessTestCase
{
    /**
     * PATCH /subscriptions/{subscriptionId}/card
     */
    public function testCard()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->createStripeCustomer('tok_fr');
        /** @var Subscription $subscription */
        $subscription = Subscription::create([
            'customer' => $user->stripe_id,
            'default_source' => $user->asStripeCustomer()->default_source,
            'items' => [
                ['plan' => 'v5_monthly'],
            ],
            'expand' => ['default_source'],
        ]);

        JWT::actingAs($user);

        $this->json('patch', "/subscriptions/{$subscription->id}/card", [
            'card' => ['token' => 'tok_es'],
        ]);

        $response = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->assertOpenAPIResponse($this->response);
        $this->assertNotEquals(
            $subscription->default_source->fingerprint,
            $response->data->payment_method->fingerprint
        );
    }
}

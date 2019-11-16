<?php

declare(strict_types=1);

namespace Tests\Regression;

use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Stripe\Subscription;
use Tests\APIStatelessTestCase;
use function factory;

/**
 * @link https://github.com/mathrix-education/education-api/issues/54
 */
class Issue54Test extends APIStatelessTestCase
{
    public function testCardOverrideOnCreation(): void
    {
        /** @var User $user */
        $this->requester = factory(User::class)->create();
        $this->requester->createStripeCustomer('tok_fr'); // This should not require 3DS2
        JWT::actingAs($this->requester);

        $this->json(
            'post',
            '/subscriptions',
            [
                'plan' => 'v5_annually',
                'card' => ['token' => 'tok_threeDSecure2Required'], // This should require 3DS2
            ]
        );

        $this->assertResponseStatus(202);
    }

    public function testSubscriptionCardOverrideOnUpdate(): void
    {
        /** @var User $user */
        $this->requester = factory(User::class)->create();
        $this->requester->createStripeCustomer('tok_fr'); // This should not require 3DS2
        JWT::actingAs($this->requester);

        $subscription = Subscription::create([
            'customer'       => $this->requester->stripe_id,
            'default_source' => $this->requester->asStripeCustomer()->default_source,
            'items'          => [
                ['plan' => 'v5_monthly'],
            ],
            'metadata'       => [
                'user_id' => $this->requester->id,
            ],
        ]);

        $this->json(
            'patch',
            "/subscriptions/{$subscription->id}/card",
            [
                'card' => ['token' => 'tok_threeDSecure2Required'], // This should require 3DS2
            ]
        );

        $this->assertResponseStatus(202);
    }
}

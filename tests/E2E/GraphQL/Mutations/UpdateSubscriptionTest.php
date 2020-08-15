<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function dispatch_now;
use function factory;

/**
 * @testdox Mutation updateSubscription
 */
class UpdateSubscriptionTest extends GraphQLIntegrationTestCase
{
    protected Dispatcher   $dispatcher;
    protected StripeClient $client;
    protected ModeMachine  $modeMachine;

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatcher  = $this->app->make(Dispatcher::class);
        $this->client      = $this->app->make(StripeClient::class);
        $this->modeMachine = $this->app->make(ModeMachine::class);
    }

    /**
     * @testdox changes the payment method of a subscription
     *
     * @throws ApiErrorException
     */
    public function testPaymentMethodReplacement(): void
    {
        $user            = $this->actingAsGetUser();
        $paymentMethodId = 'pm_card_fr';
        $customer        = $user->ensureStripeCustomer($paymentMethodId);
        $user->refresh();

        $subscription = $this->client->subscriptions->create([
            'customer'               => $customer->id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['price' => $this->modeMachine->get('laser_month')->id],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
            'expand'                 => ['default_payment_method'],
        ]);

        $previousFingerPrint = $subscription->default_payment_method->card->fingerprint;
        dispatch_now(new UpsertSubscription($subscription));

        $this->graphql(
            <<<'GRAPHQL'
            mutation($input: UpdateSubscription!) {
                updateSubscription(input: $input) {
                    id
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'id'              => $subscription->id,
                    'paymentMethodId' => 'pm_card_ch',
                ],
            ],
        );

        $subscription = $this->client->subscriptions->retrieve($subscription->id, [
            'expand' => ['default_payment_method'],
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertNotEquals(
            $previousFingerPrint,
            $subscription->default_payment_method->card->fingerprint,
        );
    }

    /**
     * @testdox denies the access to a subscription if the user does not own it
     *
     * @throws ApiErrorException
     */
    public function testNotOwner(): void
    {
        /** @var User $user */
        $user            = factory(User::class)->create();
        $paymentMethodId = 'pm_card_fr';
        $customer        = $user->ensureStripeCustomer($paymentMethodId);
        $user->refresh();

        $subscription = $this->client->subscriptions->create([
            'customer'               => $customer->id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['price' => $this->modeMachine->get('laser_month')->id],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
        ]);

        $this->dispatcher->dispatchNow(new UpsertSubscription($subscription));

        $this->withPermissions();

        $this->graphql(
            <<<'GRAPHQL'
            mutation($input: UpdateSubscription!) {
                updateSubscription(input: $input) {
                    id
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'id'              => $subscription->id,
                    'paymentMethodId' => 'foo',
                ],
            ],
        );

        $this->assertResponseStatus(200);
        $this->assertErrorCategory('internal');
    }
}

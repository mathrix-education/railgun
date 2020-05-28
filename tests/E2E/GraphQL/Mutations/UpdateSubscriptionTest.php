<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Billing\Models\Plan;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function dispatch_now;
use function factory;

/**
 * @testdox Mutation updateSubscription
 */
class UpdateSubscriptionTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox changes the payment method of a subscription
     *
     * @throws ApiErrorException
     */
    public function testPaymentMethodReplacement(): void
    {
        $user            = $this->withPermissions();
        $paymentMethodId = 'pm_card_fr';
        $customer        = $user->createCustomer($paymentMethodId);
        $user->refresh();

        $subscription = Subscription::create([
            'customer'               => $customer->id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['plan' => Plan::MONTHLY_PLAN],
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

        $subscription = Subscription::retrieve([
            'id'     => $subscription->id,
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
        $customer        = $user->createCustomer($paymentMethodId);
        $user->refresh();

        $subscription = Subscription::create([
            'customer'               => $customer->id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['plan' => Plan::MONTHLY_PLAN],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
        ]);

        dispatch_now(new UpsertSubscription($subscription));

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

        $this->assertResponseOk();
        $this->assertErrorCode('OWNERSHIP');
        $this->assertErrorCategory('auth');
    }
}

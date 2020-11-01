<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Illuminate\Contracts\Bus\Dispatcher;
use Railgun\Billing\Stripe\Repositories\PricesRepository;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\BraintreeGenerator;
use Tests\Utils\StripeGenerator;

class UpdateSubscriptionPaymentMethodTest extends GraphQLTestCase
{
    protected Dispatcher       $dispatcher;
    protected PricesRepository $prices;
    protected StripeClient     $client;
    protected StripeClient     $clientLegacy;
    protected Gateway          $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatcher   = $this->app->make(Dispatcher::class);
        $this->prices       = $this->app->make(PricesRepository::class);
        $this->client       = $this->app->make(StripeClient::class);
        $this->clientLegacy = $this->app->make(StripeClientRegistry::class)->make(StripeClientRegistry::CLIENT_LEGACY);
        $this->gateway      = $this->app->make(Gateway::class);
    }

    protected function execute(string $id, string $paymentMethodId): void
    {
        $this->graphQL(
            <<<'GRAPQHL'
            mutation($input: UpdateSubscription!) {
                updateSubscription(input: $input) {
                    id
                    plan
                    customer_id
                    status
                    current_period_start
                    current_period_end
                    created_at
                    updated_at
                    canceled_at
                    card {
                        name
                        brand
                        exp_month
                        exp_year
                        last4
                    }
                }
            }
            GRAPQHL,
            ['input' => ['id' => $id, 'paymentMethodId' => $paymentMethodId]],
        );
    }

    /**
     * @throws ApiErrorException
     */
    public function testStripeLegacyMigrationSuccess(): void
    {
        $user    = $this->actingAsGetUser();
        $context = StripeGenerator::subscription([
            'user'   => $user,
            'client' => StripeClientRegistry::CLIENT_LEGACY,
        ]);
        /** @var Subscription $previous */
        $previous = $context['subscription'];

        $this->execute($previous->id, 'pm_card_fr');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($user->premium);
        $this->assertEquals(2, $user->subscriptions->count());

        $stripeSubscription = $this->clientLegacy->subscriptions->retrieve($previous->id);

        $this->assertEquals(StripeSubscription::STATUS_CANCELED, $stripeSubscription->status);
        $this->seeInDatabase('subscriptions', [
            'id'     => $stripeSubscription->id,
            'status' => StripeSubscription::STATUS_CANCELED,
        ]);

    }

    /**
     * Migrate subscription with a v4_* plan with a successful setup intent.
     *
     * @throws NotFound
     */
    public function testBraintreeMigrationSuccess(): void
    {
        $user = $this->actingAsGetUser();

        $context = BraintreeGenerator::subscription(['user' => $user]);
        /** @var Subscription $previous */
        $previous = $context['subscription'];

        $this->execute($previous->id, 'pm_card_fr');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($user->premium);
        $this->assertEquals(2, $user->subscriptions->count());

        $btSubscription = $this->gateway->subscription()->find($previous->id);

        $this->assertEquals(BraintreeSubscription::CANCELED, $btSubscription->status);
        $this->seeInDatabase('subscriptions', [
            'id'     => $btSubscription->id,
            'status' => StripeSubscription::STATUS_CANCELED,
        ]);
    }

    /**
     * Migrate subscription with a v4_* plan with a card triggering 3D Secure.
     *
     * @throws NotFound
     */
    public function testBraintreeMigration3DS2(): void
    {
        $user = $this->actingAsGetUser();

        $context = BraintreeGenerator::subscription(['user' => $user]);
        /** @var Subscription $previous */
        $previous = $context['subscription'];

        $this->execute($previous->id, 'pm_card_threeDSecure2Required');

        $this->assertResponseOk();
        $this->assertErrorCode('THREE_D_SECURE');

        $this->assertTrue($user->premium);
        $this->assertEquals(1, $user->subscriptions->count());

        $btSubscription = $this->gateway->subscription()->find($previous->id);

        $this->assertEquals(BraintreeSubscription::ACTIVE, $btSubscription->status);
        $this->seeInDatabase('subscriptions', [
            'id'     => $btSubscription->id,
            'status' => StripeSubscription::STATUS_ACTIVE,
        ]);
    }
}

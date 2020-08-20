<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Test\Nonces;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\Subscription
 */
class SubscriptionTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * Get a Stripe subscription from the primary account.
     *
     * @param string $paymentMethodId The payment method id to use.
     *
     * @return StripeSubscription The generated Stripe subscription instance.
     *
     * @throws ApiErrorException
     */
    protected function getStripeSubscription(string $paymentMethodId): StripeSubscription
    {
        /** @var StripeClient $client */
        $client = $this->app->make(StripeClient::class);
        /** @var ModeMachine $modeMachine */
        $modeMachine = $this->app->make(ModeMachine::class);
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->ensureStripeCustomer($paymentMethodId);

        return $client->subscriptions->create([
            'customer'               => $user->stripe_id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [['price' => $modeMachine->get(ModeMachine::PLAN_MONTH)->id]],
        ]);
    }

    /**
     * Get a Stripe subscription from the legacy account.
     *
     * @param string $paymentMethodId The payment method id to use.
     *
     * @return StripeSubscription The generated Stripe subscription instance.
     *
     * @throws ApiErrorException
     */
    protected function getStripeLegacySubscription(string $paymentMethodId): StripeSubscription
    {
        /** @var StripeClient $client */
        $client = $this->app->make(StripeClientRegistry::class)->make(StripeClientRegistry::CLIENT_LEGACY);
        /** @var User $user */
        $user     = factory(User::class)->create();
        $customer = $client->customers->create(['email' => $user->email]);

        $user->stripe_id = $customer->id;
        $user->save();

        $paymentMethod = $client->paymentMethods->retrieve($paymentMethodId);
        $client->paymentMethods->attach($paymentMethod->id, ['customer' => $customer->id]);

        return $client->subscriptions->create([
            'customer'               => $user->stripe_id,
            'default_payment_method' => $paymentMethod->id,
            'items'                  => [['plan' => 'v5_monthly']],
        ]);
    }

    /**
     * @param string $paymentMethodId The Braintree payment method nonce to use ({@see Nonces}).
     *
     * @return BraintreeSubscription The generated Braintree subscription instance.
     */
    protected function getBraintreeLegacySubscription(
        string $paymentMethodId
    ): BraintreeSubscription
    {
        /** @var Gateway $gateway */
        $gateway = $this->app->make(Gateway::class);
        /** @var User $user */
        $user     = factory(User::class)->create();
        $customer = $gateway->customer()->create(['email' => $user->email])->customer;

        $paymentMethod = $gateway->paymentMethod()->create([
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $paymentMethodId,
        ])->paymentMethod;

        return $gateway->subscription()->create([
            'id'                 => $this->faker->regexify('sub_[0-9a-f]{32}'),
            'planId'             => 'v4_monthly',
            'paymentMethodToken' => $paymentMethod->token,
        ])->subscription;
    }

    /**
     * @covers ::user
     */
    public function testUserStripe(): void
    {
        $subscriptionId = $this->faker->regexify('sub_[a-zA-Z0-9]{14}');
        $customerId     = $this->faker->regexify('cus_[a-zA-Z0-9]{14}');
        $anchor         = Carbon::now()->addDays(15);

        $user = factory(User::class)->create(['stripe_id' => $customerId]);

        $subscription = new Subscription([
            'id'                   => $subscriptionId,
            'plan'                 => ModeMachine::PLAN_MONTH,
            'customer_id'          => $customerId,
            'status'               => StripeSubscription::STATUS_ACTIVE,
            'current_period_start' => $anchor->subMonth(),
            'current_period_end'   => $anchor,
            'user_id'              => $user->id,
            'canceled_at'          => null,
        ]);
        $subscription->save();

        $this->assertTrue($subscription->user->is($user));
    }

    /**
     * @covers ::user
     */
    public function testUserBraintree(): void
    {
        $subscriptionId = $this->faker->regexify('sub_[0-9a-f]{32}');
        $customerId     = $this->faker->regexify('cus_[0-9a-f]{32}');
        $anchor         = Carbon::now()->addDays(15);

        $user = factory(User::class)->create(['braintree_id' => $customerId]);

        $subscription = new Subscription([
            'id'                   => $subscriptionId,
            'plan'                 => ModeMachine::PLAN_MONTH,
            'customer_id'          => $customerId,
            'status'               => StripeSubscription::STATUS_ACTIVE,
            'current_period_start' => $anchor->subMonth(),
            'current_period_end'   => $anchor,
            'user_id'              => $user->id,
            'canceled_at'          => null,
        ]);
        $subscription->save();

        $this->assertTrue($subscription->user->is($user));
    }

    /**
     * @testWith ["laser_month", "stripe"]
     *           ["v3_monthly", "stripe_legacy"]
     *           ["v4_monthly", "braintree_legacy"]
     * @covers ::getProcessorAttribute
     *
     * @param string $plan              The subscription id.
     * @param string $expectedProcessor The expected processor.
     */
    public function testGetProcessorAttribute(string $plan, string $expectedProcessor): void
    {
        if (Str::contains('stripe', $expectedProcessor)) {
            $id = $this->faker->regexify('sub_[0-9a-z]{14}');
        } else {
            $id = $this->faker->regexify('sub_[0-9a-f]{32}');
        }

        $subscription = new Subscription(['id' => $id, 'plan' => $plan]);

        $this->assertEquals($expectedProcessor, $subscription->processor);
    }

    /**
     * @covers ::getLatestInvoiceAttribute
     *
     * @throws ApiErrorException
     */
    public function testGetLatestInvoiceStripe(): void
    {
        $subscription = Subscription::fromStripe(
            $this->getStripeSubscription('pm_card_fr'),
        );

        $this->assertEquals(Subscription::PROCESSOR_STRIPE, $subscription->processor);
        $this->assertInstanceOf(Invoice::class, $subscription->latest_invoice);
    }

    /**
     * @covers ::getLatestInvoiceAttribute
     *
     * @throws ApiErrorException
     */
    public function testGetLatestInvoiceStripeLegacy(): void
    {
        $subscription = Subscription::fromStripe(
            $this->getStripeLegacySubscription('pm_card_fr'),
        );

        $this->assertEquals(Subscription::PROCESSOR_STRIPE_LEGACY, $subscription->processor);
        $this->assertNotNull($subscription->latest_invoice);
    }

    /**
     * @covers ::getLatestInvoiceAttribute
     */
    public function testGetLatestInvoiceBraintree(): void
    {
        $subscription = Subscription::fromBraintree(
            $this->getBraintreeLegacySubscription(Nonces::$transactableVisa),
        );

        $this->assertEquals(Subscription::PROCESSOR_BRAINTREE_LEGACY, $subscription->processor);
        $this->assertNotNull($subscription->latest_invoice);
    }

    /**
     * @covers ::getCardAttribute
     *
     * @throws ApiErrorException
     */
    public function testGetCardStripe(): void
    {
        $subscription = Subscription::fromStripe(
            $this->getStripeSubscription('pm_card_fr'),
        );

        $this->assertEquals(Subscription::PROCESSOR_STRIPE, $subscription->processor);
        $this->assertIsArray($subscription->card);
    }

    /**
     * @covers ::getCardAttribute
     *
     * @throws ApiErrorException
     */
    public function testGetCardStripeLegacy(): void
    {
        $subscription = Subscription::fromStripe(
            $this->getStripeLegacySubscription('pm_card_fr'),
        );

        $this->assertEquals(Subscription::PROCESSOR_STRIPE_LEGACY, $subscription->processor);
        $this->assertIsArray($subscription->card);
    }

    /**
     * @covers ::getCardAttribute
     */
    public function testGetCardBraintreeLegacy(): void
    {
        $subscription = Subscription::fromBraintree(
            $this->getBraintreeLegacySubscription(Nonces::$transactableVisa),
        );

        $this->assertEquals(Subscription::PROCESSOR_BRAINTREE_LEGACY, $subscription->processor);
        $this->assertIsArray($subscription->card);
    }

    /**
     * @covers ::getCardAttribute
     */
    public function testGetCardBraintreeLegacyPaypal(): void
    {
        $subscription = Subscription::fromBraintree(
            $this->getBraintreeLegacySubscription(Nonces::$paypalBillingAgreement),
        );

        $this->assertEquals(Subscription::PROCESSOR_BRAINTREE_LEGACY, $subscription->processor);
        $this->assertNull($subscription->card);
    }
}

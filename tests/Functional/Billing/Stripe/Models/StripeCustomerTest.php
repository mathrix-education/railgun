<?php

declare(strict_types=1);

namespace Tests\Functional\Billing\Stripe\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @covers \Railgun\Billing\Stripe\Models\StripeCustomer
 */
class StripeCustomerTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    /**
     * @throws ApiErrorException
     */
    public function testCreate(): void
    {
        $user = factory(User::class)->create();

        $user->ensureStripeCustomer();

        $this->assertNotNull($user->stripe_id);
        $this->assertEmpty($user->stripe_cards);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }

    /**
     * @throws ApiErrorException
     */
    public function testCreateWithCard(): void
    {
        $user            = factory(User::class)->create();
        $paymentMethodId = 'pm_card_visa';

        $user->ensureStripeCustomer($paymentMethodId);

        $this->assertNotNull($user->stripe_id);
        $this->assertNotEmpty($user->stripe_cards);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }

    /**
     * @throws ApiErrorException
     */
    public function testMigrate(): void
    {
        $user = factory(User::class)->create(['stripe_id' => 'cus_migrate']);

        $user->ensureStripeCustomer();

        $this->assertNotNull($user->stripe_id);
        $this->assertNotEquals('cus_migrate', $user->stripe_id);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }

    /**
     * @throws ApiErrorException
     */
    public function testUpdateUsername(): void
    {
        $user = factory(User::class)->create();

        $user->ensureStripeCustomer();

        $user->username = $this->faker->userName;
        $user->save();

        $this->assertNotNull($user->stripe_id);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }

    /**
     * @throws ApiErrorException
     */
    public function testUpdateEmail(): void
    {
        $user = factory(User::class)->create();

        $user->ensureStripeCustomer();

        $this->assertEquals($user->email, $user->stripe_customer->email);
        $previousEmail = $user->email;

        $user->email = $this->faker->email;
        $user->save();

        $this->assertNotEquals($previousEmail, $user->stripe_customer->email);
        $this->assertEquals($user->email, $user->stripe_customer->email);
    }

    /**
     * @throws ApiErrorException
     */
    public function testUpdateCard(): void
    {
        $user = factory(User::class)->create();

        $user->ensureStripeCustomer();
        $paymentMethodId = 'pm_card_visa';
        $user->ensureStripeCustomer($paymentMethodId);

        $this->assertNotNull($user->stripe_id);
        $this->assertNotEmpty($user->stripe_cards);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }

    /**
     * @throws ApiErrorException
     */
    public function testUpdateDuplicateCard(): void
    {
        $user = factory(User::class)->create();

        $paymentMethodId = 'pm_card_visa';
        $user->ensureStripeCustomer($paymentMethodId);
        $user->ensureStripeCustomer($paymentMethodId);

        $this->assertNotNull($user->stripe_id);
        $this->assertNotEmpty($user->stripe_cards);
        $this->assertCount(1, $user->stripe_cards);
        $this->seeInDatabase('users', ['stripe_id' => $user->stripe_id]);
    }
}
